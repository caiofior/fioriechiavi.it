<?php
namespace login\user;
/**
 * User class
 *
 * @author caiofior
 */
class User extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'user');
   }
   /**
    * Loads user from confirm code
    * @param string $confirmCode
    */
   public function loadFromConfirmCode($confirmCode) {
        $data = $this->table->select(array('confirm_code'=>$confirmCode))->current();
        if (is_object($data))
            $this->data = $data->getArrayCopy();
   }
   /**
    * Create the profile before saving the user
    */
   public function insert() {
      $profile = new \login\user\Profile($this->db);
      $profile->setData(array(
         'email'=>$this->data['username'] 
      ));
      $profile->insert();
      $this->setData($profile->getData('id'), 'profile_id');
      parent::insert();
   }
   /**
    * Gets the associated Profile
    * @return \login\user\Profile
    */
    public function getProfile()
    {
      $profile = new \login\user\Profile($this->db);
      if (array_key_exists('role_id', $this->data)) {
          $profile->loadFromId($this->data['profile_id']);
      }
      return $profile;
    }
    /**
     * Gets the user role object
     * @return \login\user\UserRole
     */
    public function getRole()
    {
      $userRole = new \login\user\UserRole($this->db);
      if (array_key_exists('role_id', $this->data)) {
          $userRole->loadFromId($this->data['role_id']);
      }
      return $userRole;
    }
    /**
     * Resets user password
     */
    public function resetPassword() {
        $n = 6;
        $password = '';
        for ($c = 0; $c < 6 ; $c++)
        $password .= ((rand(1,4) != 1) ? chr(rand(97, 122)) : rand(0, 9));
        
         ob_start();
         require $this->db->baseDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'recover.php';
         $html = new \Zend\Mime\Part(ob_get_clean());
         $html->type = 'text/html';

         $body = new \Zend\Mime\Message();
         $body->setParts(array($html));

         $message = new \Zend\Mail\Message();
         $message
            ->addTo($this->data['username'])
            ->addFrom($GLOBALS['config']->mail_from)
            ->setSubject('Recupero password del sito '.$GLOBALS['config']->siteName)
            ->setBody($body);
         $GLOBALS['transport']->send($message);

        $this->data['password']=md5($password);
        $this->update();
       
    }

}