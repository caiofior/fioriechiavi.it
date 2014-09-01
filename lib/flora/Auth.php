<?php
namespace flora;
if (!interface_exists('\Zend\Authentication\Adapter\AdapterInterface')) {
      require ('/usr/share/php/vendor/zendframework/zendframework/library/Zend/Authentication/Adapter/AdapterInterface.php');
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
    * Set up the calss
    * @param \Zend\Db\Adapter\Adapter $db
    * @param string $username
    * @param string $password
    */
   public function __construct($db,$username,$password) {
      $this->db=$db;
      $this->username=$username;
      $this->password=$password;
   }
   /**
    * Autenticates the user
    * @return \Zend\Authentication\Result
    */
   public function authenticate() {
      extract($this->db->query('
      SELECT COUNT(`username`) as isAuthenticated FROM `user` WHERE 
      `active` = 1 AND
      `username`="'.addslashes($this->username).'" AND
      `password`="'.addslashes(md5($this->password)).'"
      ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy());
      
      if($isAuthenticated > 0)
         $code = \Zend\Authentication\Result::SUCCESS;
      else
         $code =  \Zend\Authentication\Result::FAILURE;
      return new \Zend\Authentication\Result($code, $_REQUEST['username']);
   }

}
