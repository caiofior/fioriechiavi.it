<?php
namespace flora\dico;
/**
 * Taka dicotomic key class
 *
 * @author caiofior
 */
class Dico extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'dico');
   }
}