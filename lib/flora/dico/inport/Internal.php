<?php
namespace flora\dico\inport;
/**
 * Imports the data from the internal format
 */
class Internal implements \flora\dico\inport\Inport {
   /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function inport  (\flora\dico\DicoItemColl $dicoItemColl, $stream) {
      $dicoItemColl->emptyColl();
      $cols = array('id','text','taxa_id');
      while($row = fgetcsv($stream,1000,"\t")) {
         $row = array_combine($cols, $row);
         $row['id']=trim($row['id']);
         $dicoItem = $dicoItemColl->addItem();
         $dicoItem->setData($row);
      }
      return $dicoItemColl;
   }
}

