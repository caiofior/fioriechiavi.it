<?php
namespace flora\dico\export;
/**
 * Exports the data in the internal format
 */
class Internal {
   /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function export (\flora\dico\DicoItemColl $dicoItemColl, $stream) {
      foreach ($dicoItemColl->getItems() as $dicoItem) {
         fwrite($stream,$dicoItem->getData('id'));
         fwrite($stream,"\t");
         fwrite($stream,$dicoItem->getData('text'));
         fwrite($stream,PHP_EOL);
      }
   }
}

