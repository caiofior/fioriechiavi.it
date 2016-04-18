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
      $select = $this->content->getTable()->getSql()->select();
      $select = $this->setFilter($select,$criteria);
      $select->columns(array('count'=>new \Zend\Db\Sql\Expression('COUNT(*)')));
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
          'profile_email'=>new \Zend\Db\Sql\Expression('(SELECT `email` FROM `profile` WHERE `profile`.`id`=`taxa_observation`.`profile_id`)'),
          'point'=>new \Zend\Db\Sql\Expression('asText(position)')
      ));
      if (array_key_exists('valid', $criteria) && $criteria['valid'] != '') {
          $select->where('`taxa_observation`.`valid` = '.intval($criteria['valid']));
      }
      if (array_key_exists('taxa_id', $criteria) && $criteria['taxa_id'] != '' ) {
          $this->taxa_id=intval($criteria['taxa_id']);
          $select->where('`taxa_observation`.`taxa_id` = '.intval($criteria['taxa_id']));
      }
      if (array_key_exists('profile_id', $criteria) && $criteria['profile_id'] != '') {
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
     /**
      * Return a Multipoint with Added observation points
      * @return \MultiPoint
      */
     public function getMultiPoint () {
         $pointArray = array();
         foreach($this->items as $observation) {
	     if (is_object($observation->getPoint())) {
             	$pointArray[] = $observation->getPoint();
             } else {
                 throw new \Exception('Invalid observation coordinates', 1508121236);
             }
         }
         return new \MultiPoint($pointArray);
     }
}