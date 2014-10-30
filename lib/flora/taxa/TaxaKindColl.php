<?php
namespace flora\taxa;
/**
 * Description of Taxa Kind Coll
 *
 * @author caiofior
 */
class TaxaKindColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \flora\taxa\TaxaKind($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       if (array_key_exists('term', $criteria) && $criteria['term'] != '') {
          $criteria['sSearch']=$criteria['term'];
       }
       if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where(' ( `initials` LIKE "'.addslashes($criteria['sSearch']).'%" OR `name` LIKE "'.addslashes($criteria['sSearch']).'%" ) ');
       }
       $select->order('ord');
       return $select;
    }
}