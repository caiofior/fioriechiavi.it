<?php
namespace contact\contact;
/**
 * Contact class
 *
 * @author caiofior
 */
class Contact extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'contact');
   }
  
}