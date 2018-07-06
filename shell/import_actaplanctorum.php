<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$mysql = $GLOBALS['db'];
$prog = 1;
$baseUrl = 'http://www.actaplantarum.org/flora/flora_info.php?id=';
$mysql->query('TRUNCATE `actaplanctorum`', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
$missing = 0;
while ($missing<100) {
   $content =  file_get_contents($baseUrl.$prog);
   preg_match("/<div class='testo5'[^>]*>(.*)/ism", $content,$matches);
   if (key_exists(1, $matches)) {
      $missing = 0;
      $matches = preg_replace("/<\/b>.*/ism", '',$matches[1]);
      preg_match_all("/<i>([^<]*)<\/i>/",$matches,$matches);
      
      $matches = implode(' ',$matches[1]);
      $matches = strtolower(trim(strip_tags($matches)));
      
      
      if (preg_match_all("/dryades.units.it\/floritaly\/index.php\?procedure=taxon_page&tipo=all&id=([0-9]+)/",$content,$floritalyId)) {
         $floritalyId = intval($floritalyId[1][0]);
      } else {
         $floritalyId =0;   
      }
      
      echo $prog.' '.$matches.PHP_EOL;
      $mysql->query('REPLACE `actaplanctorum` (`name`,`id`,`floritaly_id`) VALUES ("'. addslashes($matches).'",'.$prog.','.$floritalyId.')', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   } else {
      $missing++;
      echo $prog.' mancante'.PHP_EOL;
   }
   sleep(1);
   $prog++;
} 


