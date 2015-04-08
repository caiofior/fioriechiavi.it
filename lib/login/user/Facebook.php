<?php
namespace login\user;
/**
 * Facebook class
 *
 * @author caiofior
 */
class Facebook extends \Content implements \login\user\User
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'facebook');
   }
   /**
    * Saves las login datetime
    * @param string $id
    */
   public function loadFromId($id) {
      parent::loadFromId($id);
      if (array_key_exists('userid', $this->data) && $this->data['userid'] != '')  {
         $this->data['last_login_datetime']=date('Y-m-d H:i:s');
         $this->update();
      }
   }
   
   public function update() {
       $this->data['last_login_datetime']=date('Y-m-d H:i:s'); 
       if (array_key_exists('expiresIn', $this->rawData) && $this->rawData['expiresIn'] != '')  {
            $this->data['expires_datetime']=date('Y-m-d H:i:s', time()+$this->rawData['expiresIn']);  
       }
       parent::update();
   }

   /**
    * Create the profile before saving the user
    */
   public function insert() {
      $this->data['creation_datetime']=date('Y-m-d H:i:s');
      $this->data['last_login_datetime']=date('Y-m-d H:i:s');
      if (array_key_exists('expiresIn', $this->rawData) && $this->rawData['expiresIn'] != '')  {
            $this->data['expires_datetime']=date('Y-m-d H:i:s', time()+$this->rawData['expiresIn']);  
      }
      parent::insert();
   }
   /**
    * Gets the associated Profile
    * @return \login\user\Profile
    */
    public function getProfile()
    {
      $profile = new \login\user\Profile($this->db);
      if (array_key_exists('profile_id', $this->data)) {
        $profile->loadFromId($this->data['profile_id']);
      }
      return $profile;
    }
    /**
     * Sets Graph value
     * @param string $label
     * @param string $value
     * @param string $accessToken
     */
    public function setGraphvalue($label,$value,$accessToken='') {
         $this->db->query('REPLACE INTO `facebook_graph` 
        SET `userId`="' . $this->data['userID'].'",
            `label` ="' . addslashes($label).'",
            `value` ="' . addslashes($value).'",
            `accessToken` ="' . addslashes($accessToken).'",
            `last_update_datetime`=NOW()
            ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
    /**
     * Gets graph data
     * @return array
     */
    public function getGraphValues() {
        return $this->db->query('SELECT * FROM `facebook_graph` 
        WHERE `userId`="' . $this->data['userID'].'"', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->toArray();
    }
}