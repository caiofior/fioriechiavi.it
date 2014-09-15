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
      $dicoItremColl = new \flora\dico\DicoItemColl($this->db);
      $dicoItremColl->loadAll(array('id_dico'=>$this->data['id']));
      if ($dicoItremColl->count() == 0) {
         for ($c = 0 ; $c < 2; $c++) {
            $dicoItem = $dicoItremColl->addItem();
            $dicoItem->setData($c, 'id');
            $dicoItem->setData(true, 'incomplete');
         }
      }
      return $dicoItremColl;
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