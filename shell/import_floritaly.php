<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$mysql = $GLOBALS['db'];
$prog = 1;
$baseUrl = 'http://dryades.units.it/floritaly/index.php?procedure=taxon_page&tipo=all&id=';
$mysql->query('TRUNCATE `floritaly`', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
$missing = 0;
while ($missing<100) {
   $content =  file_get_contents($baseUrl.$prog);
   preg_match("/\<title\>([^\<]*)\<\/title\>/ism", $content,$matches);
   if (key_exists(1, $matches)) {
      $missing = 0;
      
      $words = explode(' ', strtolower($matches[1]));
      if (sizeof($words)>2) {      
         echo $prog.' '.$words[0].' '.$words[1].PHP_EOL;
         $mysql->query('REPLACE `floritaly` (`name`,`id`) VALUES ("'. addslashes($words[0].' '.$words[1]).'",'.$prog.')', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
      }
   } else {
      $missing++;
      echo $prog.' mancante'.PHP_EOL;
   }
   sleep(1);
   $prog++;
} 


