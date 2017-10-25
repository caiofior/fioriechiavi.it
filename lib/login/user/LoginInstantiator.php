<?php
namespace login\user;
/**
 * Instantiates the user class
 *
 * @author caiofior
 */
class LoginInstantiator implements \login\user\UserInstantiator {
   /**
    * Instantiates the user class
    * @param \Zend\Db\Adapter\Adapter $db
    * @param String $login
    * @return \login\user\User
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
    * @return \login\user\User
    */
   public static function createLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login,$password) {
      $user = self::getLoginInstance($db, $login);
      if (is_object ($user) && $user->getData('username') != '') {
         throw new \Exception('Username already used '.$login,1409011238);
      }
      $adminColl = new \login\user\LoginColl($db);
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
      $GLOBALS['mail']->msgHTML(ob_get_clean());
      $GLOBALS['mail']->Subject = 'Registrazione sul sito '.$GLOBALS['config']->siteName;
      $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
      $GLOBALS['mail']->addAddress($login, $GLOBALS['config']->siteName);
      $GLOBALS['mail']->send();

      
      if ($adminColl->count() > 0 ) {
         ob_start();
         require $db->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_user.php';
         $GLOBALS['mail']->msgHTML(ob_get_clean());
         $GLOBALS['mail']->Subject = 'Registrazione sul sito '.$GLOBALS['config']->siteName;
         $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
         $GLOBALS['mail']->addAddress($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
         $GLOBALS['mail']->send();
                 
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
      $login = new \login\user\Login($db);
      $login->loadFromConfirmCode($confirmCode);
      $profile = $login->getProfile();
      if($login->getData('username') == '') {
         throw new \Exception('Utente non valido '.$login->getData('username'),1409011509);
      }
      if($profile->getData('active') == '1') {
         throw new \Exception('Utente già confermato '.$login->getData('username'),1409011510);
      }
      $profile->setData(array(
              'active'=>1
      ));
      $profile->update();
      return $login;
   }
}
