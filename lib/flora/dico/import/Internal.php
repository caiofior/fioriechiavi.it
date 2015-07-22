<?php
namespace flora\dico\import;
/**
 * Imports the data from the internal format
 */
class Internal implements \flora\dico\import\Import {
   /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function import  (\flora\dico\DicoItemColl $dicoItemColl, $stream) {
      $dicoItemColl->emptyColl();
      $cols = array('id','text','taxa_id');
      while($row = fgetcsv($stream,1000,"\t")) {
         while (sizeof($row)<sizeof($cols)) {
             array_push($row,'');
         }
         while (sizeof($row)>sizeof($cols)) {
             array_pop($row);
         }
         $row = array_combine($cols, $row);
         $row['id']=trim($row['id']);
         $dicoItem = $dicoItemColl->addItem();
         $dicoItem->setData($row);
      }
      return $dicoItemColl;
   }
}

