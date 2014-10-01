<?php
namespace flora\taxa;
/**
 * Description of Taxa Coll
 *
 * @author caiofior
 */
class TaxaColl extends \ContentColl {
      /**
       * Relation with th content
       * @param type $db
       */
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
       $select->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id',array('taxa_kind_initials'=>'initials','taxa_kind_id_name'=>'name'), \Zend\Db\Sql\Select::JOIN_LEFT);
       if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where('`taxa`.`name` LIKE "%'.addslashes($criteria['sSearch']).'%"', \Zend\Db\Sql\Predicate\PredicateSet::OP_OR);
          $select->where('`taxa`.`description` LIKE "%'.addslashes($criteria['sSearch']).'%"', \Zend\Db\Sql\Predicate\PredicateSet::OP_OR);
       }
       return $select;
    }
}