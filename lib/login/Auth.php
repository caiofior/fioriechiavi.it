<?php
namespace login;
if (!interface_exists('\Zend\Authentication\Adapter\AdapterInterface')) {
      require('/usr/share/php/vendor/zendframework/zend-authentication/Zend/Authentication/Adapter/AdapterInterface.php');
}

class Auth implements \Zend\Authentication\Adapter\AdapterInterface {
   /**
    * Db adapter reference
    * @var \Zend\Db\Adapter\Adapter
    */
   protected $db;
   /**
    * Username
    * @var string
    */
   protected $username;
   /**
    * Password
    * @var string
    */
   protected $password;
   /**
    * Autenticate from token
    * @var string
    */
   protected $token;
   /**
    * Set up the calss
    * @param \Zend\Db\Adapter\Adapter $db
    * @param string $username
    * @param string $password
    */
   public function __construct($db,$username,$password,$token=null) {
      $this->db=$db;
      $this->username=$username;
      $this->password=$password;
      $this->token=$token;
   }
   /**
    * Autenticates the user
    * @return \Zend\Authentication\Result
    */
   public function authenticate() {
      if (is_null($this->token)) {
         extract($this->db->query('
         SELECT COUNT(`username`) as isAuthenticated, username FROM `login` WHERE 
         (SELECT `active` FROM `profile` WHERE `login`.`profile_id`=`profile`.`id`) = 1 AND
         `username`="'.addslashes($this->username).'" AND
         `password`="'.addslashes(md5($this->password)).'"
         ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy());
      } else {
         extract($this->db->query('
         SELECT COUNT(`username`) as isAuthenticated, username FROM `login` WHERE 
         (SELECT `active` FROM `profile` WHERE `login`.`profile_id`=`profile`.`id`) = 1 AND
         (SELECT `token` FROM `profile` WHERE `login`.`profile_id`=`profile`.`id`) = "'.addslashes($this->token).'"
         ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy());
      }
      
      if($isAuthenticated > 0)
         $code = \Zend\Authentication\Result::SUCCESS;
      else
         $code =  \Zend\Authentication\Result::FAILURE;
      
      return new \Zend\Authentication\Result($code, $username);
      
   }

}
