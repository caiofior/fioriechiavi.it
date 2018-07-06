<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$mysql = $GLOBALS['db'];
$linkRes = $mysql->query('SELECT * FROM `link_taxa` WHERE `provider_id`=1', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
foreach($linkRes->toArray() as $link) {
   if (preg_match('/([0-9]+)/',$link['link'],$id)) {
      $flItRes = $mysql->query('SELECT * FROM `actaplanctorum` WHERE `id`='.intval(reset($id)), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      $flIt = $flItRes->toArray();
      $flIt = reset($flIt);
      $url = 'http://dryades.units.it/floritaly/index.php?procedure=taxon_page&tipo=all&id='.$flIt['floritaly_id'];
      $flItRes = $mysql->query('INSERT IGNORE INTO `link_taxa` SET
         `provider_id`=4,
         `taxa_id`='.intval($link['taxa_id']).',
         `link`="'.$url.'",
         `datetime`=NOW()  
         ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   }
}

