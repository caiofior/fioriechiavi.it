<?php
namespace flora\region;
/**
 * Taka class
 *
 * @author caiofior
 */
class Region extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'region');
   }
  
}