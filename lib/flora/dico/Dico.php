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
    * Returns a collection of dicotomic key items
    * @return \flora\dico\DicoItemColl
    */
   public function getDicoItemColl () {
      $dicoItemColl = new \flora\dico\DicoItemColl($this->db);
      $dicoItemColl->loadAll(array('id_dico'=>$this->data['id']));
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
            if ($siblingDicoItemColl->count() == 0) {
               $dicoItem = $dicoItemColl->addItem();
               $dicoItem->setData($siblingCode, 'id');
               $dicoItem->setData(true, 'incomplete');
            }
            $taxaId = $dicoItem->getRawData('taxa_id');
            if ($taxaId == '') {
               $childrenCodeArray = $dicoItem->getChildrenCodeArray();
               foreach($childrenCodeArray as $childrenCode) {
                  $childrenDicoItemColl = $dicoItemColl->filterByAttributeValue($childrenCode, 'id');
                  if ($childrenDicoItemColl->count() == 0) {
                     $dicoItem = $dicoItemColl->addItem();
                     $dicoItem->setData($childrenCode, 'id');
                     $dicoItem->setData(true, 'incomplete');
                  }      
               } 
            }
         }
      }
      $dicoItemColl->sort(array(
          'field'=>'id'
      ));
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
      $dicoItem->setData($value,'value');
      $dicoItem->replace();
      $dicoItem->loadFromIdAndDico($this->data['id'],$id_dico_item);
      return $value;
   }
}