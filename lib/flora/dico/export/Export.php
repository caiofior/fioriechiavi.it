<?php
namespace flora\dico\export;
/**
 * Exports the Dico data
 */
interface Export {
    /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function export (\flora\dico\DicoItemIntColl  $dicoItemColl, $stream);
}
