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
    * Export the dicotomy to 
    * @param string $format
    * @param resource $stream
    */
   public function export ($format,$stream) {
      if (gettype($stream) != 'resource') {
         throw new \Exception('Stream resource must be provided',1410081107);
      }
      if(!interface_exists('flora\dico\export\Export')) {
         require __DIR__.'/export/Export.php';
      }
      switch ($format) {
         case 'internal':
            if(!class_exists('flora\dico\export\Internal')) {
               require __DIR__.'/export/Internal.php';
            }
            $exportClass = new \flora\dico\export\Internal();
            break;
         case 'pignatti':
            if(!class_exists('flora\dico\export\Pignatti')) {
               require __DIR__.'/export/Pignatti.php';
            }
            $exportClass = new \flora\dico\export\Pignatti();
            break;
         default :
            throw new \Exception('No output format is provided',1410081107);
         break;
      }
      $dicoItemColl = $this->getDicoItemColl();
      $exportClass->export($dicoItemColl,$stream);
   }
   /**
    * Imports Dico Item from data stream
    * 
    * @param type $stream
    * @throws \Exception
    */
   public function import ($format,$stream) {
      if (gettype($stream) != 'resource') {
         throw new \Exception('Stream resource must be provided',1410081107);
      }
      if(!interface_exists('flora\dico\inport\Inport')) {
         require __DIR__.'/inport/Inport.php';
      }
      switch ($format) {
         case 'internal':
            if(!class_exists('flora\dico\inport\Internal')) {
               require __DIR__.'/inport/Internal.php';
            }
            $inportClass = new \flora\dico\inport\Internal();
            break;
         case 'pignatti':
            if(!class_exists('flora\dico\inport\Pignatti')) {
               require __DIR__.'/inport/Pignatti.php';
            }
            $inportClass = new \flora\dico\inport\Pignatti();
            break;
         default :
            throw new \Exception('No input format is provided',1410081107);
         break;
      }
      $dicoItemColl =  $inportClass->inport($this->getDicoItemColl(),$stream);
      $dicoItemColl->setDicoId($this->data['id']);
      return $dicoItemColl;
   }
   /**
    * Imports Dico Item from data stream and saves it
    * 
    * @param type $stream
    * @throws \Exception
    */
   public function importAndSave( $format,$stream) {
      $this->emptyDicoItems();
      $dicoItemColl = $this->import($format, $stream);
      foreach($dicoItemColl->getItems() as $dicoItem) {
         $dicoItem->replace();
      } 
   }
   /**
    * Deletes all dico item associated
    */
   public function emptyDicoItems() {
      if (array_key_exists('id', $this->data)) {
         $this->db->query('DELETE FROM `dico_item` 
         WHERE id_dico = '.addslashes($this->data['id'])
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      }
   }
   /**
    * Deletes item
    */
   public function delete() {
      $this->db->query('UPDATE `taxa` SET `dico_id` = NULL
          WHERE `dico_id` = '.$this->data['id']
          , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      parent::delete();
   }
}