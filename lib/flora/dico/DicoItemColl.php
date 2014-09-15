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
       if (
               key_exists('id_dico', $criteria) &&
               $criteria['id_dico'] != '') {
          $select->where('id_dico = '.intval($criteria['id_dico']));
       }
       $select->order('id_dico asc, id asc');
       return $select;
    }
}