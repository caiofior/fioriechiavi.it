<?php
namespace login\user;
/**
 * Instantiates the user class
 *
 * @author caiofior
 */
class FacebookInstantiator implements \login\user\UserInstantiator  {
   /**
    * Instantiates the user class
    * @param \Zend\Db\Adapter\Adapter $db
    * @param String $login
    * @return \login\user\User
    */
   public static function getLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login) {
      $fb = new \login\user\Facebook($db);
      $fb->loadFromId($login);
      if (sizeof($fb->getData()) == 0)
         $fb = null;
      return $fb;
   }
   /**
    * Creates a new user
    * @param \Zend\Db\Adapter\Adapter $db
    * @param type $login
    * @param type $password
    * @return \login\user\User
    */
   public static function createLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login,$password) {
        $fb = new \login\user\Facebook($db);
        $fb->loadFromId($login['userID']);
        $profile=$fb->getProfile();
        $insert = $fb->getData('userID') == '';
        $fb->setData($login);
        if ($insert) {
            $profile->setData(array(
                'active'=>1,
                'role_id'=>3
            ));
            $profile->insert();
            $fb->setData($profile->getData('id'), 'profile_id');
            $fb->insert();
        } else {
            if ($profile->getData('active') != 1) {
                throw new \Exception('User not active',1504151435);
            }
            $fb->update();
        }
        return $fb;
   }
}
