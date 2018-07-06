<?php
namespace flora\linkprovider\provider;
/**
 * Get plant reference from actaplanctorum site
 */
class FlorItaly implements \flora\linkprovider\provider\Provider {
   /**
    * Get plant reference from actaplanctorum site
    */
   public function retrive (\flora\taxa\Taxa $taxa) {
      $resultSet = $taxa->getDb()->query('SELECT `id` FROM `floritaly` WHERE `name` = "'. addslashes(trim(strtolower($taxa->getData('name')))).'" LIMIT 1', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      $resultSet = $resultSet->toArray();
      if (sizeof($resultSet)>0) {
         return 'http://dryades.units.it/floritaly/index.php?procedure=taxon_page&tipo=all&id='.intval(current($resultSet)['id']);
      }
      return false;
   }
}

