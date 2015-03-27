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
       * Check if default regions are loaded
       * @param array $criteria
       */
      public function loadAll(array $criteria=null) {
          parent::loadAll($criteria);
          if (sizeof($criteria) == 0 && $this->count() == 0) {
              $defaultRegionFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                  .DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'region.sql';
              if (is_file($defaultRegionFile)) {
                $this->content->getDb()->query(file_get_contents($defaultRegionFile), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
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
       $select = $this->setFilter($select,$criteria);
       $select->order('id');
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
        if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where(' ( `id` LIKE "'.addslashes($criteria['sSearch']).'%" OR `name` LIKE "'.addslashes($criteria['sSearch']).'%" ) ');
       }
      return $select;
    }
}