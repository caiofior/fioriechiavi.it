<?php
namespace dictionary;
/**
 * Description of Term Image Coll
 *
 * @author caiofior
 */
class TermImageColl extends \ContentColl {
      /**
       * Taxa id
       * @var int
       */
      private $term_id;
      /**
       * Relation with th content
       * @param type $db
       */
      public function __construct($db) {
         parent::__construct(new \dictionary\TermImage($db));
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       if (array_key_exists('term_id', $criteria)) {
          $this->term_id=intval($criteria['term_id']);
          $select->where('`term_image`.`term_id` = '.intval($criteria['term_id']));
       }
       return $select;
    }
    /**
   * Add new item to the collection
   * @return \Content
   */
     public function addItem($key = null) {
        $item = parent::addItem($key);
        $item->setData($this->term_id, 'term_id');
        return $item;
     }
}