<?php
namespace flora\dico;
/**
 * Description of Add Dico Coll
 *
 * @author caiofior
 */
class AddDicoColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \flora\dico\AddDico($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       $select = $this->setFilter($select, $criteria);
       return $select;
    }
    
     /**
     * Sets the filter
     * @param \Zend\Db\Sql\Select $select
     * @param array $criteria
     * @return \Zend\Db\Sql\Select
     */
    private function setFilter ($select,$criteria) {
      if (array_key_exists('taxa_id', $criteria) && $criteria['taxa_id'] != '') {
          $select->where(' `taxa_id` = '.intval($criteria['taxa_id']));
      }
      if (array_key_exists('used_taxa_id', $criteria) && $criteria['used_taxa_id'] != '') {
          $select->where(' `id`  IN (SELECT `dico_id` FROM `add_dico_item` WHERE `taxa_id`= '.intval($criteria['used_taxa_id']).')  ');
      }
      return $select;
    }
}