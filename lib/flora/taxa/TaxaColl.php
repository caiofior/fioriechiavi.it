<?php
namespace flora\taxa;
/**
 * Description of Taxa Coll
 *
 * @author caiofior
 */
class TaxaColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \flora\taxa\Taxa($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       if (key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where('`name` LIKE "%'.addslashes($criteria['sSearch']).'%"', \Zend\Db\Sql\Predicate\PredicateSet::OP_OR);
          $select->where('`description` LIKE "%'.addslashes($criteria['sSearch']).'%"', \Zend\Db\Sql\Predicate\PredicateSet::OP_OR);
       }
       return $select;
    }
}