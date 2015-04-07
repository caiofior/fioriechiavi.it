<?php
namespace login\user;
/**
 * Description of UserColl
 *
 * @author caiofior
 */
class ProfileRoleColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \login\user\ProfileRole($db));
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
      return $select;
    }
}