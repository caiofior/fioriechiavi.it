<?php
namespace flora\taxa;
/**
 * Taka class
 *
 * @author caiofior
 */
class TaxaAttribute extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa_attribute');
   }
   /**
    * Loads the attribute from its name
    * @param string $name
    */
   public function loadFromName($name) {
      $data = $this->table->select(array('name'=>$name))->current();
      if (is_object($data))
         $this->data = $data->getArrayCopy();
   }
}