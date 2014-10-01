<?php
namespace flora\taxa;
/**
 * Description of Taxa Coll
 *
 * @author caiofior
 */
class TaxaAttributeColl extends \ContentColl {
      /**
       * Relation with th content
       * @param type $db
       */
      public function __construct($db) {
         parent::__construct(new \flora\taxa\TaxaAttribute($db));
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       $select->join('taxa_attribute_value', 'taxa_attribute.id=taxa_attribute_value.id_taxa_attribute',array('value'), \Zend\Db\Sql\Select::JOIN_LEFT);
       if (array_key_exists('taxa_id', $criteria)) {
          $select->where('`taxa_attribute_value`.`id_taxa` = '.intval($criteria['taxa_id']));
       } else if (array_key_exists('exclude_taxa_id', $criteria)) {
          $select->where(' `taxa_attribute`.`id` NOT IN (SELECT `id_taxa_attribute` FROM `taxa_attribute_value` WHERE `id_taxa` = '.intval($criteria['exclude_taxa_id']).')');
       }
       return $select;
    }
}