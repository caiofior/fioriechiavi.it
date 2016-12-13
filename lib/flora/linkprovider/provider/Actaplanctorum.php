<?php
namespace flora\linkprovider\provider;
/**
 * Get plant reference from actaplanctorum site
 */
class Actaplanctorum implements \flora\linkprovider\provider\Provider {
   /**
    * Get plant reference from actaplanctorum site
    */
   public function retrive (\flora\taxa\Taxa $taxa) {
      $resultSet = $taxa->getDb()->query('SELECT `id` FROM `actaplanctorum` WHERE `name` = "'. addslashes(trim(strtolower($taxa->getData('name')))).'" LIMIT 1', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      $resultSet = $resultSet->toArray();
      if (sizeof($resultSet)>0) {
         return 'http://www.actaplantarum.org/flora/flora_info.php?id='.intval(current($resultSet)['id']);
      }
      return false;
   }
}

