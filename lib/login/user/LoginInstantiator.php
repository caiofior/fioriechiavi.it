<?php
namespace login\user;
/**
 * Instantiates the user class
 *
 * @author caiofior
 */
class LoginInstantiator {
   /**
    * Instantiates the user class
    * @param \Zend\Db\Adapter\Adapter $db
    * @param String $login
    * @return \login\user\Login
    */
   public static function getLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login) {
      $user = new \login\user\Login($db);
      $user->loadFromId($login);
      if (sizeof($user->getData()) == 0)
         $user = null;
      return $user;
   }
   /**
    * Creates a new user
    * @param \Zend\Db\Adapter\Adapter $db
    * @param type $login
    * @param type $password
    * @return \login\user\Login
    */
   public static function createLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login,$password) {
      $user = self::getLoginInstance($db, $login);
      if (is_object ($user) && $user->getData('username') != '') {
         throw new \Exception('Username already used '.$login,1409011238);
      }
      $adminColl = new \login\user\UserColl($db);
      $adminColl->loadAll(array('role_id'=>3));
      $role_id = 3;
      $role_description='User';
      $active = 0;
      if ($adminColl->count() == 0 ) {
         $role_id = 1;
         $role_description='Administrator';
         $active = 1;
      }
      
      $profileRole = new \login\user\ProfileRole($db);
      $profileRole->loadFromId($role_id);
      if($profileRole->getData('id') != $role_id ) {
          $defaultRuleFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                  .DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'profile_role.sql';
          if (is_file($defaultRuleFile)) {
            $db->query(file_get_contents($defaultRuleFile), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
          }
          $profileRole->loadFromId($role_id);
          if($profileRole->getData('id') != $role_id ) {
            $profileRole->setData(array(
                'id'=>$role_id,
                'description'=>$role_description
            ));
            $profileRole->insert();
          }
      }
      $user = new \login\user\Login($db);
      $user->setData(array(
              'username'=>$login,
              'password'=>md5($password),
              'creation_datetime'=>date('Y-m-d H:i:s'),
              'confirm_code'=>md5(serialize($_SERVER).time())
              ));
      $user->insert();
      $profile = $user->getProfile();
      $profile->setData(array(
              'role_id'=>$role_id,
              'active'=>$active
              ));
      $profile->update();
      ob_start();
      require $db->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'register.php';
      $html = new \Zend\Mime\Part(ob_get_clean());
      $html->type = 'text/html';

      $body = new \Zend\Mime\Message();
      $body->setParts(array($html));

      $message = new \Zend\Mail\Message();
      $message
         ->addTo($login)
         ->addFrom($GLOBALS['config']->mail_from)
         ->setSubject('Registrazione sul sito '.$GLOBALS['config']->siteName)
         ->setBody($body);
      $GLOBALS['transport']->send($message);
      
      if ($adminColl->count() > 0 ) {
         ob_start();
         require $db->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'register.php';
         $html = new \Zend\Mime\Part(ob_get_clean());
         $html->type = 'text/html';

         $body = new \Zend\Mime\Message();
         $body->setParts(array($html));

         $message = new \Zend\Mail\Message();
         foreach($adminColl->getItems() as $admin) {
            $message->addTo($admin->getData('username'));
         }
                 
         $message->addFrom($GLOBALS['config']->mail_from)
            ->setSubject('Registrazione sul sito '.$GLOBALS['config']->siteName)
            ->setBody($body);
         $GLOBALS['transport']->send($message);
      }
      return $user;
   }
   /**
    * Confirms a user from confirm code
    * @param \Zend\Db\Adapter\Adapter $db
    * @param string $confirmCode
    * @return \login\user\Login
    */
   public static function confirmLoginInstance(\Zend\Db\Adapter\Adapter $db ,$confirmCode) {
      $user = new \login\user\Login($db);
      $user->loadFromConfirmCode($confirmCode);
      $profile = $user->getProfile();
      if($user->getData('username') == '') {
         throw new \Exception('Utente non valido '.$user->getData('username'),1409011509);
      }
      if($profile->getData('active') == '1') {
         throw new \Exception('Utente già confermato '.$user->getData('username'),1409011510);
      }
      $profile->setData(array(
              'active'=>1
      ));
      $profile->update();
      return $user;
   }
}
