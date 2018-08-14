<?php
namespace flora\linkprovider;
/**
 * Link to google search
 *
 * @author caiofior
 */
class GoogleLink extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      set_error_handler(function(){},E_USER_WARNING);
      parent::__construct($db, 'google_search');
      restore_error_handler();
   }
}
