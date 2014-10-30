<?php
namespace login\user;
/**
 * Description of UserColl
 *
 * @author caiofior
 */
class UserColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \login\user\User($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       $select = $this->setFilter($select,$criteria);
       return $select;
    }
       /**
     * Count items
     * @return int
     */
    public function countAll($criteria = array()) {
      $select = $this->content->getTable()->getSql()->select()->columns(array(new \Zend\Db\Sql\Expression('COUNT(*)')));
      $select = $this->setFilter($select,$criteria);
      $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
      $results = $statement->execute();
      $resultSet = new \Zend\Db\ResultSet\ResultSet();
      $resultSet->initialize($results);
      $data = $resultSet->current()->getArrayCopy();
      return intval(array_pop($data));
    }
    /**
     * Sets the filter
     * @param \Zend\Db\Sql\Select $select
     * @param array $criteria
     * @return \Zend\Db\Sql\Select
     */
    private function setFilter ($select,$criteria) {
      $select->join('profile', 'profile.id=user.profile_id', array('profile.first_name'=>'first_name','profile.last_name'=>'last_name'), \Zend\Db\Sql\Select::JOIN_LEFT);
      if (array_key_exists('role_id', $criteria) && $criteria['role_id'] != '') {
          $select->where('`role_id` = '.intval($criteria['role_id']));
      }
      if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
         $select->where(' ( `profile`.`first_name` LIKE "'.addslashes($criteria['sSearch']).'%" OR `profile`.`last_name` LIKE "'.addslashes($criteria['sSearch']).'%" OR `username` LIKE "'.addslashes($criteria['sSearch']).'%" ) ');
      }
      return $select;
    }
}
