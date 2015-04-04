<?php
namespace flora\taxa;
/**
 * Description of Taxa Image Coll
 *
 * @author caiofior
 */
class TaxaImageColl extends \ContentColl {
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
         parent::__construct(new \flora\taxa\TaxaImage($db));
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       if (array_key_exists('taxa_id', $criteria)) {
          $this->taxa_id=intval($criteria['taxa_id']);
          $select->where('`taxa_image`.`id_taxa` = '.intval($criteria['taxa_id']));
       }
       return $select;
    }
    /**
   * Add new item to the collection
   * @return \Content
   */
     public function addItem($key = null) {
        $item = parent::addItem($key);
        $item->setData($this->taxa_id, 'id_taxa');
        return $item;
     }
}