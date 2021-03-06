<?php
namespace login\user;
/**
 * Description of UserColl
 *
 * @author caiofior
 */
class LoginColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \login\user\Login($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       $select->columns(array(
                '*',
                'role_id' => new \Zend\Db\Sql\Predicate\Expression('
                ( SELECT `role_id` FROM `profile` WHERE `profile`.`id` = `login`.`profile_id` )
               ')
            ));
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
      if (array_key_exists('profile_id', $criteria) && $criteria['profile_id'] != '') {
          $select->where('`profile_id` = '.intval($criteria['profile_id']));
      }
      if (array_key_exists('role_id', $criteria) && $criteria['role_id'] != '') {
          $select->having('`role_id` = '.intval($criteria['role_id']));
      }
      return $select;
    }
}
