<?php
namespace flora\taxa;
/**
 * Description of Taxa Coll
 *
 * @author caiofior
 */
class TaxaColl extends \ContentColl {
     /**
     * Filter for profile
     * @var login\user\profile 
     */
      private $filterForTaxaprofile=null;
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
          if ( $this->count() == 0 && 
               !array_key_exists('moreDicoItems', $criteria) &&
               !array_key_exists('images', $criteria) &&
               !array_key_exists('doNotCreate', $criteria) &&
                  
                    (
                      !array_key_exists('iDisplayStart', $criteria) ||
                      $criteria['iDisplayStart']==0
                    ) &&
                    (
                      !array_key_exists('sSearch', $criteria) ||
                      $criteria['sSearch']==''
                    )

                  ){
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
       * Filters taxa for profile
       * @param \login\user\Profile $filterForTaxaprofile
       */
      public function filterForProfile(\login\user\Profile $filterForTaxaprofile) {
          $this->filterForTaxaprofile=$filterForTaxaprofile;
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       $select ->columns(array('*',
                    'status' => new \Zend\Db\Sql\Predicate\Expression('
                (               
                    IFNULL(LENGTH(`taxa`.`description`),0)+
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0'),
                    'taxa_kind_initials' => new \Zend\Db\Sql\Predicate\Expression('(SELECT `initials` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` )'),
                    'taxa_kind_name' => new \Zend\Db\Sql\Predicate\Expression('(SELECT `name` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` )'),            
                ));
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
         $select->where(' `taxa`.`name` LIKE "'.addslashes($criteria['sSearch']).'%" ');
      }
      if (array_key_exists('images', $criteria) && $criteria['images'] != '') {
         if ($criteria['images'] == 0 ) {
            $select->where(' (SELECT COUNT(`taxa_image`.`id`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`) = 0 ');
         } else {
            $select->where(' (SELECT COUNT(`taxa_image`.`id`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`) = 1 ');
         }
      }
      if (array_key_exists('rand', $criteria) && $criteria['rand'] != '') {
          $resultSet = $this->content->getDb()->query('SELECT MAX(`id`) FROM `taxa`'
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
          $resultSet = current(current($resultSet->toArray()));
          
          $taxaIds = array();
          for ($c=0; $c < 10; $c++) {
             $taxaIds[] = rand(1,$resultSet); 
          }
         $select->where(new \Zend\Db\Sql\Predicate\Expression('`taxa`.`id` IN ('.implode(',',$taxaIds).')'));
      }
      if (array_key_exists('moreDicoItems', $criteria) && $criteria['moreDicoItems'] != '') {
         $select->columns(array('*','dico_item_count'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT COUNT(*) FROM `dico_item` WHERE  `dico_item`.`parent_taxa_id`=`taxa`.`id` )')));
         $initials = explode(':',$criteria['moreDicoItems']);
         $select->where(' (SELECT `initials` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) IN ("'.  implode('","', $initials).'")');
         $select->order('dico_item_count DESC');
      }
      if (
               array_key_exists('status', $criteria) &&
               $criteria['status'] == true) {
         $select->where('
                (               
                    IFNULL(LENGTH(`taxa`.`description`),0)+
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0
             '); 
         
      }
      if (array_key_exists('used_taxa_id', $criteria) && $criteria['used_taxa_id'] != '') {
          $select->where(' `id`  IN (SELECT `parent_taxa_id` FROM `dico_item` WHERE `taxa_id`= '.intval($criteria['used_taxa_id']).')  ');
      }
      if (is_object($this->filterForTaxaprofile)) {
          if($this->filterForTaxaprofile->getEditableTaxaColl()->count() >0) {
	          $select->join('taxa_search','taxa.id=taxa_search.taxa_id', \Zend\Db\Sql\Select::SQL_STAR, \Zend\Db\Sql\Select::JOIN_LEFT);
	          $sqlFilter = '( FALSE';
	          foreach($this->filterForTaxaprofile->getEditableTaxaColl()->getItems() as $taxa) {
	              $resultSet = $this->content->getDb()->query('SELECT `lft`,`rgt` FROM `taxa_search` 
	              WHERE `taxa_id` = '. intval($taxa->getData('id'))
	                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
	              $resultSet = $resultSet->toArray();
	              $taxaSearch = array_shift($resultSet);
	              $sqlFilter .= ' OR (`lft` >= '.$taxaSearch['lft'].' AND `rgt` <= '.$taxaSearch['rgt'].')';
	          }
	          $sqlFilter .= ' )';
	          $select->where($sqlFilter);
	  }
      }
      return $select;
    }
}