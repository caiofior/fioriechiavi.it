<?php
namespace flora\user;
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
      $profile = new \flora\user\Profile($this->db);
      $profile->setData(array(
         'email'=>$this->data['username'] 
      ));
      $profile->insert();
      $this->setData($profile->getData('id'), 'profile_id');
      parent::insert();
   }
   // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getProfile
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_user_Profile
     */
    public function getProfile()
    {
        $returnValue = null;

        // section 127-0-1-1-651afd3b:147fc7005b0:-8000:0000000000000884 begin
        // section 127-0-1-1-651afd3b:147fc7005b0:-8000:0000000000000884 end

        return $returnValue;
    }

    /**
     * Short description of method getRule
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_user_UserRole
     */
    public function getRule()
    {
        $returnValue = null;

        // section 127-0-1-1-651afd3b:147fc7005b0:-8000:0000000000000886 begin
        // section 127-0-1-1-651afd3b:147fc7005b0:-8000:0000000000000886 end

        return $returnValue;
    }

}