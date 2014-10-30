<?php
namespace flora\taxa;
/**
 * Description of Taxa Attribute Coll
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
       if (array_key_exists('taxa_id', $criteria)) {
          $select->columns(array('*','value'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT `value` FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`id_taxa_attribute`=`taxa_attribute`.`id` AND `taxa_attribute_value`.`id_taxa`= '.intval($criteria['taxa_id']).' LIMIT 1)')));
          $select->where(' `taxa_attribute`.`id` IN (SELECT `id_taxa_attribute` FROM `taxa_attribute_value` WHERE `id_taxa` = '.intval($criteria['taxa_id']).')');
       }
       if (array_key_exists('term', $criteria) && $criteria['term'] != '') {
          $criteria['sSearch']=$criteria['term'];
       }
       if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where(' `name` LIKE "'.addslashes($criteria['sSearch']).'%" ');
       }
      return $select;
    }
}