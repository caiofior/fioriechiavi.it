<?php
namespace flora\dico;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Taxa dicotomic key item interface
 *
 * @author caiofior
 */
interface DicoItemInt {
   /**
    * Max code depth
    */
   const maxCode='1';
   /**
     * Replaces dico item data
     */
   public function replace();
   /**
    * Returns the sibling code
    * @return string
    */
   public function getSiblingCode();
   /**
    * Returns an array with childen codes
    * @return array
    */
   public function getChildrenCodeArray ();
   /**
    * Load data fro dico id and id
    * @param type $dico_id
    * @param type $id
    */
   public function loadFromIdAndDico($dico_id,$id);
   /**
    * Removes the association with taxa
    */
   public function removesTaxaAssociation();
}