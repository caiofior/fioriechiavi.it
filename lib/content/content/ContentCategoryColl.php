<?php
namespace content\content;
/**
 * Description of Taxa Coll
 *
 * @author caiofior
 */
class ContentCategoryColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \content\content\ContentCategory($db));
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where(' ( `name` LIKE "%'.addslashes($criteria['sSearch']).'%" OR `description` LIKE "%'.addslashes($criteria['sSearch']).'%" ) ');
       }
       return $select;
    }
}