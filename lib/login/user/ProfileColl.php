<?php
namespace login\user;
/**
 * Description of UserColl
 *
 * @author caiofior
 */
class ProfileColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \login\user\Profile($db));
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
     * Sets the filter
     * @param \Zend\Db\Sql\Select $select
     * @param array $criteria
     * @return \Zend\Db\Sql\Select
     */
    private function setFilter ($select,$criteria) {
      $select->columns(array(
          '*'
          ));
      $select->join('profile_role', 'profile_role.id=profile.role_id', array('description'), \Zend\Db\Sql\Select::JOIN_LEFT);
      $select->join('login', 'login.profile_id=profile.id', array('*'), \Zend\Db\Sql\Select::JOIN_LEFT);
      if (array_key_exists('role_id', $criteria) && $criteria['role_id'] != '') {
          $select->where('`profile`.`role_id` = '.intval($criteria['role_id']));
      }
      if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
         $select->where(' ( `profile`.`first_name` LIKE "'.addslashes($criteria['sSearch']).'%" OR `profile`.`last_name` LIKE "'.addslashes($criteria['sSearch']).'%" OR `username` LIKE "'.addslashes($criteria['sSearch']).'%" ) ');
      }
      return $select;
    }
}
