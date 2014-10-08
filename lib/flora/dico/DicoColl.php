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
        LEFT JOIN `taxa` ON `taxa`.`dico_id`=`dico`.`id`
        WHERE `taxa`.`dico_id` IS NULL
        ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy());
       if ($dico_count == 0) {
          $dico = $this->addItem();
          $dico->insert();
       } elseif ($dico_count > 1) {
         trigger_error ('Multiple root dicotomic key items found ',E_USER_WARNING);
         $count = 0;
         $dicoStmt = $this->content->getDb()->query('
         SELECT `dico`.`id` as dico_root_id FROM `dico`
         LEFT JOIN `taxa` ON dico.id=taxa.dico_id
         WHERE ISNULL(`taxa`.`name`)
         ORDER BY `dico`.`id` ASC
         ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
         while ($dico_root_id = $dicoStmt->next()->getArrayCopy()) {
            if (++$count>1 && is_numeric($dico_root_id)) {
               $this->content->getDb()->query('
               DELETE FROM `dico`
               WHERE `dico`.`id` = '.$dico_root_id.'
               ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            }
         }
       } 
       $select->columns(array(
           'id',
           'taxa.name'=>new \Zend\Db\Sql\Predicate\Expression('IF(ISNULL(`taxa`.`name`),"Radice",CONCAT(`taxa_kind`.`initials`," ",`taxa`.`name`))')
           ));
       $select->join('taxa', 'dico.id=taxa.dico_id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
       $select->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
       if (array_key_exists('sSearch', $criteria) && $criteria['sSearch'] != '') {
          $select->where('`taxa`.`name` LIKE "%'.addslashes($criteria['sSearch']).'%"');
       }
       return $select;
    }
}