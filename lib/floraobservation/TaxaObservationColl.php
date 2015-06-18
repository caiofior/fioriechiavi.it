<?php
namespace floraobservation;
/**
 * Description of Taxa Attribute Coll
 *
 * @author caiofior
 */
class TaxaObservationColl extends \ContentColl {
       /**
       * Taxa id
       * @var int
       */
      private $taxa_id;
      /**
       * Relation with th content
       * @param type $db
       */
      public function __construct($db) {
         parent::__construct(new \floraobservation\TaxaObservation($db));
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
      if (is_object($resultSet->current())) {
        $data = $resultSet->current()->getArrayCopy();
        $data = intval(array_pop($data));
      } else {
        $data =0;
      }
      return $data;
    }
    /**
     * Sets the filter
     * @param \Zend\Db\Sql\Select $select
     * @param array $criteria
     * @return \Zend\Db\Sql\Select
     */
    private function setFilter ($select,$criteria) {
      $select->columns(array(
          '*',
          'taxa_name'=>new \Zend\Db\Sql\Expression('(SELECT CONCAT((SELECT `initials` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id`)," ",`name`) FROM `taxa` WHERE `taxa`.`id`=`taxa_observation`.`taxa_id`)'),
          'profile_email'=>new \Zend\Db\Sql\Expression('(SELECT `email` FROM `profile` WHERE `profile`.`id`=`taxa_observation`.`profile_id`)')
      ));
      if (array_key_exists('taxa_id', $criteria)) {
          $this->taxa_id=intval($criteria['taxa_id']);
          $select->where('`taxa_observation`.`taxa_id` = '.intval($criteria['taxa_id']));
      }
      if (array_key_exists('profile_id', $criteria)) {
          $select->where('`taxa_observation`.`profile_id` = '.intval($criteria['profile_id']));
      }
      return $select;
    }
    /**
   * Add new item to the collection
   * @return \Content
   */
     public function addItem($key = null) {
        $item = parent::addItem($key);
        $item->setData($this->taxa_id, 'taxa_id');
        return $item;
     }
}