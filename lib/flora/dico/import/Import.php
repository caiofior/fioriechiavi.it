<?php
namespace flora\dico\import;
/**
 * Exports the Dico data
 */
interface Import {
    /**
    * Exports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function import (\flora\dico\DicoItemIntColl $dicoItemColl, $stream);
}
