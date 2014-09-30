<?php
namespace login\user;
/**
 * Instantiates the user class
 *
 * @author caiofior
 */
class UserInstantiator {
   /**
    * Instantiates the user class
    * @param \Zend\Db\Adapter\Adapter $db
    * @param String $login
    * @return \login\user\User
    */
   public static function getUserInstance(\Zend\Db\Adapter\Adapter $db ,$login) {
      $user = new \login\user\User($db);
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
   public static function createUserInstance(\Zend\Db\Adapter\Adapter $db ,$login,$password) {
      $user = self::getUserInstance($db, $login);
      if ($user->getData('username') != '') {
         throw new \Exception('Username already used '.$login,1409011238);
      }
      $user->setData(array(
              'username'=>$login,
              'password'=>md5($password),
              'active'=>0,
              'creation_datetime'=>date('Y-m-d H:i:s'),
              'role_id'=>3,
              'confirm_code'=>md5(serialize($_SERVER).time())
              ));
      $user->insert();
      ob_start();
      require $db->baseDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'register.php';
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
      return $user;
   }
   /**
    * Confirms a user from confirm code
    * @param \Zend\Db\Adapter\Adapter $db
    * @param string $confirmCode
    * @return \login\user\User
    */
   public static function confirmUserInstance(\Zend\Db\Adapter\Adapter $db ,$confirmCode) {
      $user = new \login\user\User($db);
      $user->loadFromConfirmCode($confirmCode);
      if($user->getData('username') == '') {
         throw new \Exception('Utente non valido '.$user->getData('username'),1409011509);
      }
      if($user->getData('active') == '1') {
         throw new \Exception('Utente già confermato '.$user->getData('username'),1409011510);
      }
      $user->setData(array(
              'active'=>1
      ));
      $user->update();
      return $user;
   }
}
