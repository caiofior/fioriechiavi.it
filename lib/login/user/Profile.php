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
    * Sets the uuid
    */
   public function insert() {
      $this->data['token']= new \Zend\Db\Sql\Predicate\Expression('UUID()');
      parent::insert();
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
    /**
     * Gets a collection of users
     *     
     */
    public function getUserColl() {
        $userColl = new \login\user\UserColl($this);
        $userColl->loadAll();
        return $userColl;
    }
    /**
     * Deletes a profile and associated users
     */
    public function delete() {
        $this->db->query('DELETE FROM `login` 
        WHERE `profile_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('DELETE FROM `facebook_graph` 
        WHERE `userID` IN (SELECT `userID` FROM `facebook` WHERE `profile_id`=' . intval($this->data['id']).')'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('DELETE FROM `facebook` 
        WHERE `profile_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        parent::delete();
    }
    /**
     * Sets editable taxa
     * @param array $taxaList
     */
    public function setEditableTaxa(array $taxaList) {
        if (array_key_exists('id', $this->data) && $this->data['id'] != '')
            $this->db->query('DELETE FROM `profile_taxa` 
              WHERE `profile_id`=' . intval($this->data['id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        foreach ($taxaList as $taxa) {
            $this->db->query('INSERT INTO `profile_taxa` 
              (`profile_id`,`taxa_id`)
              VALUES
              (' . intval($this->data['id']) . ',"' . addslashes($taxa) . '")'
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
    }
    /**
     * Sets editable taxa
     * @return \flora\taxa\TaxaColl
     */
    public function getEditableTaxaColl() {
        $taxaColl = new \flora\taxa\TaxaColl($this->db);
        $resultSet = $this->db->query('SELECT `taxa_id` FROM `profile_taxa` 
              WHERE `profile_id` = '. intval($this->data['id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $taxaOr = new \flora\taxa\Taxa($this->db);
        foreach ($resultSet->toArray() as $result) {
            $taxa = clone $taxaOr;
            $taxa->loadFromId($result['taxa_id']);
            $taxaColl->appendItem($taxa);
        }
        return $taxaColl;
    }
}