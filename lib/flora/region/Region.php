<?php
namespace flora\region;
/**
 * Region class
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