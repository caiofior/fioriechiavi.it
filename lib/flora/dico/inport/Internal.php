<?php
namespace flora\dico\inport;
/**
 * Exports the data in the internal format
 */
class Internal implements \flora\dico\inport\Inport {
   /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function inport (\flora\dico\DicoItemColl $dicoItemColl, $stream) {
      foreach ($dicoItemColl->getItems() as $dicoItem) {
         fwrite($stream,$dicoItem->getData('id'));
         fwrite($stream,"\t");
         fwrite($stream,str_replace("\t",'',$dicoItem->getData('text')));
         fwrite($stream,"\t");
         fwrite($stream,str_replace("\t",'',$dicoItem->getRawData('initials').' '.$dicoItem->getRawData('name')));
         fwrite($stream,PHP_EOL);
      }
   }
}

