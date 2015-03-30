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

}