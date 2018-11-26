<?php
namespace flora\linkprovider;
/**
 * External link provider
 *
 * @author caiofior
 */
class LinkProvider extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'link_provider');
   }
   /**
    * Loads the link from its name
    * @param string $name
    */
   public function loadFromName($name) {
      $data = $this->table->select(array('name'=>$name))->current();
      if (is_object($data))
         $this->data = $data->getArrayCopy();
   }
   /**
    * Gets data from external site
    * @param \flora\taxa\Taxa $taxa Taxa values
    * @throws \Exception
    */
   public function retriveData(\flora\taxa\Taxa $taxa) {
      if (!array_key_exists('name', $this->data) || $this->data['name'] == '') {
         throw new \Exception('Provider name is required', 1612061205);
      }
      if (!interface_exists('flora\linkprovider\provider\Provider')) {
            require __DIR__ . '/provider/Provider.php';
      }
      $retriveClass = null;
      switch ($this->data['name']) {
         case 'actaplanctorum':
         require __DIR__ . '/provider/Actaplanctorum.php';   
         $retriveClass = new \flora\linkprovider\provider\Actaplanctorum();
         break;
         case 'dryades':
         require __DIR__ . '/provider/Dryades.php';   
         $retriveClass = new \flora\linkprovider\provider\Dryades();
         break;
         case 'florae':
         require __DIR__ . '/provider/Florae.php';   
         $retriveClass = new \flora\linkprovider\provider\Florae();
         break;
         case 'floritaly':
         require __DIR__ . '/provider/FlorItaly.php';   
         $retriveClass = new \flora\linkprovider\provider\FlorItaly();
         break;
      }
      if (is_object($retriveClass)) {
         $link = $retriveClass->retrive($taxa);
         if ($link !== false && $link != '') {
            $this->rawData['link'] = $link;
            $taxa->getDb()->query('DELETE FROM `link_taxa`
               WHERE `provider_id` = '.intval($this->getData('id')).' AND `taxa_id` = '.intval($taxa->getData('id'))
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE );
            $taxa->getDb()->query('INSERT INTO `link_taxa`
               (`provider_id`,`taxa_id`,`link`,`datetime`)
               VALUES ('.intval($this->getData('id')).','.intval($taxa->getData('id')).',"'. addslashes($link).'",NOW()) ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
         }
      }
   }
   /**
    * Manualy updated the link
    * @param \flora\taxa\Taxa $taxa Taxa values
    * @param string $link
    */
   public function updateLink(\flora\taxa\Taxa $taxa, $link) {
      $taxa->getDb()->query('DELETE FROM `link_taxa`
            WHERE `provider_id` = '.intval($this->getData('id')).' AND `taxa_id` = '.intval($taxa->getData('id'))
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE );
      $taxa->getDb()->query('INSERT INTO `link_taxa`
            (`provider_id`,`taxa_id`,`link`,`datetime`,`fixed`)
            VALUES ('.intval($this->getData('id')).','.intval($taxa->getData('id')).',"'. addslashes($link).'",NOW(),TRUE) ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   }
}
