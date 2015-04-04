<?php
namespace login\user;
/**
 * User class
 *
 * @author caiofior
 */
class Login extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'login');
   }
   /**
    * Saves las login datetime
    * @param string $id
    */
   public function loadFromId($id) {
      parent::loadFromId($id);
      if (array_key_exists('username', $this->data) && $this->data['username'] != '')  {
         $this->data['last_login_datetime']=date('Y-m-d H:i:s');
         $this->update();
      }
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
      $profile->loadFromId($this->data['profile_id']);
      return $profile;
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
         require $this->db->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'recover.php';
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
    /**
     * Sets a new password
     * @param string $password
     */
    public function setPassword ($password) {
       $this->data['password']=md5($password);
       $this->update();
    }
    /**
     * Check if the password is right
     * @param string $password
     * @return bool
     */
    public function checkPassword ($password) {
       return $this->data['password']===md5($password);
    }
    /**
     * Sets the new login
     * @param string $newLogin
     */
    public function setNewLogin ($newLogin) {
      $this->data['new_username']=$newLogin;
      $this->data['confirm_code']=md5(serialize($_SERVER).time());
      $this->update();
      ob_start();
      require $this->db->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'changelogin.php';
      $html = new \Zend\Mime\Part(ob_get_clean());
      $html->type = 'text/html';

      $body = new \Zend\Mime\Message();
      $body->setParts(array($html));

      $message = new \Zend\Mail\Message();
      $message
         ->addTo($this->data['username'])
         ->addFrom($GLOBALS['config']->mail_from)
         ->setSubject('Conferma modifica email/username per accedere al sito '.$GLOBALS['config']->siteName)
         ->setBody($body);
      $GLOBALS['transport']->send($message);
    }
    /**
     * Confirms the new login
     */
    public function newLoginConfirmed() {
       if ($this->data['new_username'] == '') {
          throw new Exception('There is no new mail to confirm',1410061044);
       }
       $this->data['username']=$this->data['new_username'];
       $this->data['new_username']='';
       $this->update();
       $profile = $this->getProfile();
       $profile->setData($this->data['username'], 'email');
       $profile->update();
    }
}