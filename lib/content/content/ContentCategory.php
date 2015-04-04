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
   /**
    * Deletes contents associated to the category
    */
   public function delete() {
      $this->db->query('DELETE FROM `content` 
         WHERE `categori_id`='.intval($this->data['id'])
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      parent::delete();
   }
  
}