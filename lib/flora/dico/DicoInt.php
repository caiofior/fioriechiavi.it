<?php
namespace flora\dico;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Taxa dicotomic key interface
 *
 * @author caiofior
 */
interface DicoInt {
   /**
     * Returns a collection of dico items
     * @return \flora\dico\DicoItemColl
     */
    public function getDicoItemColl($edit = false);
}