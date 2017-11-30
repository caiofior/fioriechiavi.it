<?php
namespace flora\linkprovider\provider;
/**
 * Get plant reference from actaplanctorum site
 */
class Dryades implements \flora\linkprovider\provider\Provider {
   /**
    * Get plant reference from actaplanctorum site
    */
   public function retrive (\flora\taxa\Taxa $taxa) {
      $resultSet = $taxa->getDb()->query('SELECT `id` FROM `dryades` WHERE `name` = "'. addslashes(trim(strtolower($taxa->getData('name')))).'" LIMIT 1', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      $resultSet = $resultSet->toArray();
      if (sizeof($resultSet)>0) {
         return 'http://dryades.units.it/cercapiante/index.php?procedure=cerca2&id='.intval(current($resultSet)['id']);
      }
      return false;
   }
}

