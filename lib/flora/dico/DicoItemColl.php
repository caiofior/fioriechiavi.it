<?php
namespace flora\dico;
/**
 * Description of Dicotomic key Coll
 *
 * @author caiofior
 */
class DicoItemColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \flora\dico\DicoItem($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       $select->join('taxa', 'dico_item.taxa_id=taxa.id',array('name'), \Zend\Db\Sql\Select::JOIN_LEFT);
       $select->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id',array('initials'), \Zend\Db\Sql\Select::JOIN_LEFT);
       if (
               array_key_exists('id_dico', $criteria) &&
               $criteria['id_dico'] != '') {
          $select->where('id_dico = '.intval($criteria['id_dico']));
       }
       $select->order('id_dico asc, id asc');
       return $select;
    }
    /**
     * Sort criteria
     * @param \flora\dico\DicoItem $a
     * @param \flora\dico\DicoItem $b
     * @return int
     */
    protected static function customSort($a, $b) {
      $a = $a->getData(self::$sortCriteria['field']);
      $b = $b->getData(self::$sortCriteria['field']);
      if ($a === $b) {
         return 0;
      }
      for($c =0; $c < min(strlen($a),strlen($b)); $c++) {
         if ($a[$c] == $b[$c]) {
            continue;
         }
         return ($a[$c] < $b[$c]) ? -1 : 1;
      }
      return (strlen($a) < strlen($b)) ? -1 : 1;
    } 
     /**
     * Filter collection by attribute and value
     * @param mixed $value
     * @param mixed $field
     * @return \ContentColl
     */
    public function filterByAttributeValue($value,$field) {
       $filteredColl = clone $this;
       $filteredColl->emptyColl();
       foreach ($this->items as $item) {
          if ($item->getData($field) === $value)
             $filteredColl->appendItem ($item);
       }
       return $filteredColl;
    }
}