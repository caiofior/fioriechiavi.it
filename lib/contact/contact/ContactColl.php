<?php
namespace contact\contact;
/**
 * Description of Contact Coll
 *
 * @author caiofior
 */
class ContactColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \contact\contact\Contact($db));
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
      if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where(' ( `name` LIKE "%'.addslashes($criteria['sSearch']).'%" OR `mail` LIKE "%'.addslashes($criteria['sSearch']).'%" OR `message` LIKE "%'.addslashes($criteria['sSearch']).'%" ) ');
      }
      if (array_key_exists('ip', $criteria) && $criteria['ip'] != '') {
         $select->where('ip="'.addslashes($criteria['ip']).'"');
      }
      if (array_key_exists('recency', $criteria) && $criteria['recency'] != '') {
         $select->where('datetime > "'.addslashes(date('Y-m-d H:i:s',time()-$criteria['recency'])).'"');
      }
      return $select;
    }
}