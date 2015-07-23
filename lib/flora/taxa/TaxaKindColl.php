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
       * Check if default taxa  categoriesare loaded
       * @param array $criteria
       */
      public function loadAll(array $criteria=array()) {
          parent::loadAll($criteria);
          if (sizeof(array_filter($criteria)) == 0 && $this->count() == 0) {
              $defaultTaxaKindFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                  .DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'taxa_kind.sql';
              if (is_file($defaultTaxaKindFile)) {
                $this->content->getDb()->query(file_get_contents($defaultTaxaKindFile), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
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
       $select = $this->setFilter($select, $criteria);
       $select->order('ord');
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
      $select->where(' `id` != 1 ');
      if (array_key_exists('term', $criteria) && $criteria['term'] != '') {
         $criteria['sSearch']=$criteria['term'];
      }
      if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
         $select->where(' ( `initials` LIKE "'.addslashes($criteria['sSearch']).'%" OR `name` LIKE "'.addslashes($criteria['sSearch']).'%" ) ');
      }
      return $select;
    }
}