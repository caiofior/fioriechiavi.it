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
       * Check if default taxa are loaded
       * @param array $criteria
       */
      public function loadAll(array $criteria=null) {
          parent::loadAll($criteria);
          if (sizeof(array_filter($criteria)) == 0 && $this->count() == 0) {
              $taxaKindColl = new \flora\taxa\TaxaKindColl($this->content->getDb());
              $taxaKindColl->loadAll();
              $defaultTaxaFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                  .DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'taxa.sql';
              if (is_file($defaultTaxaFile)) {
                $this->content->getDb()->query(file_get_contents($defaultTaxaFile), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                parent::loadAll($criteria);
              }
          }
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
         $select->where(' ( `taxa`.`name` LIKE "'.addslashes($criteria['sSearch']).'%" OR `taxa`.`description` LIKE "'.addslashes($criteria['sSearch']).'%" ) ');
      }
      if (array_key_exists('images', $criteria) && $criteria['images'] != '') {
         if ($criteria['images'] == 0 ) {
            $select->where(' (SELECT COUNT(`taxa_image`.`id`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`) = 0 ');
         } else {
            $select->where(' (SELECT COUNT(`taxa_image`.`id`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`) = 1 ');
         }
      }
      if (array_key_exists('moreDicoItems', $criteria) && $criteria['moreDicoItems'] != '') {
         $select->columns(array('*','dico_item_count'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT COUNT(*) FROM `dico_item` WHERE  `dico_item`.`parent_taxa_id`=`taxa`.`id` )')));
         $initials = explode(':',$criteria['moreDicoItems']);
         $select->where(' `taxa_kind`.`initials` IN ("'.  implode('","', $initials).'")');
         $select->order('dico_item_count DESC');
      }
      if (
               array_key_exists('status', $criteria) &&
               $criteria['status'] == true) {
         $select->where('
                (               
                    IFNULL(LENGTH(taxa.description),0)+
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0
             '); 
         
      }
      return $select;
    }
}