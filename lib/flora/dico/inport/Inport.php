<?php
namespace flora\dico\inport;
/**
 * Exports the Dico data
 */
interface Inport {
    /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function inport (\flora\dico\DicoItemColl $dicoItemColl, $stream);
}
