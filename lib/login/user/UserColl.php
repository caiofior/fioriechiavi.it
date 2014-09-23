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
       $select->join('profile', 'profile.id=user.profile_id', \Zend\Db\Sql\Select::SQL_STAR, \Zend\Db\Sql\Select::JOIN_LEFT);
       return $select;
    }
}
