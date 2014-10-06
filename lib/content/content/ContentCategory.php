<?php
namespace content\content;
/**
 * Content category class
 *
 * @author caiofior
 */
class ContentCategory extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'content_category');
   }
  
}