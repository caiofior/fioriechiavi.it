<?php
namespace flora\dico;
/**
 * Description of Dicotomic key Coll
 *
 * @author caiofior
 */
class DicoColl extends \ContentColl {
      public function __construct($db) {
         parent::__construct(new \flora\dico\Dico($db));
      }
    /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
       extract($this->content->getDb()->query('
        SELECT COUNT(*) as dico_count FROM `dico`
        ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy());
       if ($dico_count == 0) {
          $dico = $this->addItem();
          $dico->insert();
       } 
       $select->columns(array(
           'id',
           'taxa.name'=>new \Zend\Db\Sql\Predicate\Expression('IF(ISNULL(`taxa`.`name`),"Radice",`taxa`.`name`)')
           ));
       $select->join('taxa', 'dico.id=taxa.dico_id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
       return $select;
    }
}