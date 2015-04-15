<?php
namespace login\user;
/**
 * Instantiates the user class
 *
 * @author caiofior
 */
interface UserInstantiator {
   /**
    * Instantiates the user class
    * @param \Zend\Db\Adapter\Adapter $db
    * @param String $login
    * @return \login\user\User
    */
   public static function getLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login);
   /**
    * Creates a new user
    * @param \Zend\Db\Adapter\Adapter $db
    * @param type $login
    * @param type $password
    * @return \login\user\User
    */
   public static function createLoginInstance(\Zend\Db\Adapter\Adapter $db ,$login,$password);
}
