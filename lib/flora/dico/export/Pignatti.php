<?php
namespace flora\dico\export;
/**
 * Exports the data in the Pignatti flora Italica Format
 */
class Pignatti implements \flora\dico\export\Export{
   /**
    * Collection of positions
    * @var array
    */
   private $positions = array();
   /**
    * Last position
    * @var int
    */
   private $lastPosition = 0;
   /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function export (\flora\dico\DicoItemColl $dicoItemColl, $stream) {
      foreach ($dicoItemColl->getItems() as $dicoItem) {
         
         $lastCharacter = substr($dicoItem->getData('id'),-1);
         if ($lastCharacter == 0) {
            $this->lastPosition++;
            $this->positions[substr($dicoItem->getData('id'),0,-1).'0']= $this->lastPosition;
            $this->positions[substr($dicoItem->getData('id'),0,-1).'1']= $this->lastPosition;
         }
         fwrite($stream,str_repeat(' ',strlen($dicoItem->getData('id'))-1));
         fwrite($stream,$this->positions[$dicoItem->getData('id')]);
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

