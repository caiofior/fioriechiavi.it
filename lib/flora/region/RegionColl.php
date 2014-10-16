<?php
namespace flora\region;
/**
 * Region Coll
 *
 * @author caiofior
 */
class RegionColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \flora\region\Region($db));
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where(' ( `id` LIKE "%'.addslashes($criteria['sSearch']).'%" OR `name` LIKE "%'.addslashes($criteria['sSearch']).'%" ) ');
       }
       $select->order('id');
       return $select;
    }
}