<?php
namespace flora\dico;
/**
 * Taka dicotomic key class
 *
 * @author caiofior
 */
class Dico extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'dico');
   }
   /**
    * Load root element
    */
   public function loadRoot() {
      extract($this->db->query('
         SELECT `dico`.`id` as dico_root_id FROM `dico`
         LEFT JOIN `taxa` ON dico.id=taxa.dico_id
         WHERE ISNULL(`taxa`.`name`)
         LIMIT 1
      ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy());
      $this->loadFromId($dico_root_id);
   }
   /**
    * Returns the associated taxa
    * @return \flora\taxa\Taxa
    */
   public function getTaxa() {
         $taxa = new \flora\taxa\Taxa($this->db);
         $taxa_stmt = $this->db->query('
         SELECT `taxa`.`id` as taxa_id
         FROM `taxa` 
         WHERE `dico_id`='.$this->data['id'].'
         LIMIT 1
      ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
      if (!is_null($taxa_stmt)) {
         extract($taxa_stmt->getArrayCopy());
         $taxa->loadFromId($taxa_id);
      }
      return $taxa;
   }

   /**
    * Returns a collection of dicotomic key items
    * @param boolean $edit Collection is loaded for editing
    * @return \flora\dico\DicoItemColl
    */
   public function getDicoItemColl ($edit=false) {
      $dicoItemColl = new \flora\dico\DicoItemColl($this->db);
      if (key_exists('id', $this->data) && $this->data['id'] != '') {
         $dicoItemColl->loadAll(array('id_dico'=>$this->data['id']));
      }
      if ($edit == true) {
         if ($dicoItemColl->count() == 0) {
            for ($c = 0 ; $c < 2; $c++) {
               $dicoItem = $dicoItemColl->addItem();
               $dicoItem->setData($c, 'id');
               $dicoItem->setData(true, 'incomplete');
            }
         } else  {
            foreach ($dicoItemColl->getItems() as $dicoItem) {
               $siblingCode = $dicoItem->getSiblingCode();
               $siblingDicoItemColl = $dicoItemColl->filterByAttributeValue($siblingCode, 'id');
               for ($c = 0; $c < (\flora\dico\DicoItem::maxCode-$siblingDicoItemColl->count()); $c++) {
                  $dicoItemSibling = $dicoItemColl->addItem();
                  $dicoItemSibling->setData($siblingCode, 'id');
                  $dicoItemSibling->setData(true, 'incomplete');
               }
               $possible_taxa = true;
               $taxaId = $dicoItem->getRawData('taxa_id');
               if ($taxaId == '') {
                  $childrenCodeArray = $dicoItem->getChildrenCodeArray();
                  foreach($childrenCodeArray as $childrenCode) {
                     $childrenDicoItemColl = $dicoItemColl->filterByAttributeValue($childrenCode, 'id');
                     if ($childrenDicoItemColl->count() == 0) {
                        $dicoItemChildren = $dicoItemColl->addItem();
                        $dicoItemChildren->setData($childrenCode, 'id');
                        $dicoItemChildren->setData(true, 'incomplete');
                     } else {
                        $possible_taxa = false;
                     }
                  }
                  if ($possible_taxa === true) {
                     $dicoItem->setData(true, 'possible_taxa');
                  }
               }
            }
         }
         $dicoItemColl->sort(array(
             'field'=>'id'
         ));
      }
      return $dicoItemColl;
   }
   /**
    * Sets dico Item value
    * @param int $id_dico_item
    * @param string $value
    * @return string
    */
   public function setDicoItemValue($id_dico_item,$value) {
      $dicoItem = new \flora\dico\DicoItem($this->db);
      $dicoItem->loadFromIdAndDico($this->data['id'],$id_dico_item);
      $dicoItem->setData($value,'text');
      $dicoItem->replace();
      $dicoItem->loadFromIdAndDico($this->data['id'],$id_dico_item);
      return $value;
   }
}