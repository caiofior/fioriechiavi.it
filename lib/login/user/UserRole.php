<?php
namespace login\user;
/**
 * User Role class
 *
 * @author caiofior
 */
class UserRole extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'user_role');
   }
}