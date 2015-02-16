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
      if (array_key_exists('term', $criteria) && $criteria['term'] != '') {
         $criteria['sSearch']=$criteria['term'];
      }
      if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
         $select->where(' ( `taxa`.`name` LIKE "%'.addslashes($criteria['sSearch']).'%" OR `taxa`.`description` LIKE "%'.addslashes($criteria['sSearch']).'%" ) ');
      }
      if (array_key_exists('images', $criteria) && $criteria['images'] != '') {
         if ($criteria['images'] == 0 ) {
            $select->where(' (SELECT COUNT(`taxa_image`.`id`) FROM `taxa_image` WHERE `taxa_image`.`id_taxa`=`taxa`.`id`) = 0 ');
         } else {
            $select->where(' (SELECT COUNT(`taxa_image`.`id`) FROM `taxa_image` WHERE `taxa_image`.`id_taxa`=`taxa`.`id`) = 1 ');
         }
      }
      if (array_key_exists('moreDicoItems', $criteria) && $criteria['moreDicoItems'] != '') {
         $select->columns(array('*','dico_item_count'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT COUNT(*) FROM `dico_item` WHERE `dico_item`.`id_dico`=`taxa`.`dico_id` )')));
         $initials = explode(':',$criteria['moreDicoItems']);
         $select->where(' `taxa_kind`.`initials` IN ("'.  implode('","', $initials).'")');
         $select->order('dico_item_count DESC');
      }
      return $select;
    }
}