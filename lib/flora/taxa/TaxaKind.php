<?php
namespace flora\taxa;
/**
 * Taka Kind class
 *
 * @author caiofior
 */
class TaxaKind extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa_kind');
   }
}