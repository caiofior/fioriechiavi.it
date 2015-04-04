<?php
namespace flora\dico\export;
/**
 * Exports the data in the internal format
 */
class Internal implements \flora\dico\export\Export {
   /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function export (\flora\dico\DicoItemColl $dicoItemColl, $stream) {
      foreach ($dicoItemColl->getItems() as $dicoItem) {
         fwrite($stream,$dicoItem->getData('id'));
         fwrite($stream,"\t");
         fwrite($stream,str_replace("\t",'',$dicoItem->getData('text')));
         fwrite($stream,"\t");
         fwrite($stream,str_replace("\t",'',$dicoItem->getData('taxa_id')));
         fwrite($stream,"\t");
         fwrite($stream,str_replace("\t",'',$dicoItem->getRawData('initials').' '.$dicoItem->getRawData('name')));
         fwrite($stream,PHP_EOL);
      }
   }
}

