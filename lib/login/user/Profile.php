<?php
namespace login\user;
/**
 * User class
 *
 * @author caiofior
 */
class Profile extends \Content
{
    /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'profile');
   }
    /**
     * Gets the user role object
     * @return \login\user\UserRole
     */
    public function getRole()
    {
      $profileRole = new \login\user\ProfileRole($this->db);
      if (array_key_exists('role_id', $this->data)) {
          $profileRole->loadFromId($this->data['role_id']);
      }
      return $profileRole;
    }
    public function getUserColl() {
        $userColl = new \login\user\UserColl($this->db);
        $userColl->loadAll(array('profile_id'=>$this->data['id']));
        return $userColl;
    }
    public function delete() {
        $this->db->query('DELETE FROM `user` 
        WHERE `profile_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        parent::delete();
    }

}