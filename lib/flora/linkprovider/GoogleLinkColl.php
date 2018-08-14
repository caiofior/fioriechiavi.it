<?php
namespace flora\linkprovider;
/**
 * Collection of google links
 *
 * @author caiofior
 */
class GoogleLinkColl extends \ContentColl {
      /**
       * Taxa object
       * @var \flora\taxa\Taxa
       */
      protected $taxa;
      /**
       * Relation with the content
       * @param type $db
       */
      public function __construct($db) {
         parent::__construct(new \flora\linkprovider\GoogleLink($db));
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
      if (array_key_exists('taxa_id', $criteria) && $criteria['taxa_id'] != '') {
         $select->where(' `taxa_id` = '.intval($criteria['taxa_id']));
      }
      return $select;
    }
}
