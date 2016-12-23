<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$mysql = $GLOBALS['db'];
$prog = 1;
$baseUrl = 'http://dbiodbs.units.it/carso/chiavi_pub26?spez=';
$mysql->query('TRUNCATE `dryades`', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
$missing = 0;
while ($missing<100) {
   $content =  @file_get_contents($baseUrl.$prog);
   preg_match("/<span class=\"style38\"[^>]*>(.*)<table>/ism", $content,$matches);  
   if (key_exists(0, $matches)) {
      $missing = 0;
      $matches = strtolower(trim(strip_tags($matches[0])));
      $matches = explode(' ',$matches);
      if (key_exists(1, $matches) && $matches[1] == 'sect.') {
         $prog++;
         continue;
      }
      if (key_exists(1, $matches) && $matches[1] == 'x') {
         $matches[1]=$matches[2];
         unset($matches[2]);
      }
      if(sizeof($matches)>2) {
         $matches = array_splice($matches, 0,2);
      }
      $matches = implode(' ',$matches);
      $matches = str_replace('*','',$matches);
      echo $prog.' '.$matches.PHP_EOL;
      $mysql->query('REPLACE `dryades` (`name`,`id`) VALUES ("'. addslashes($matches).'",'.$prog.')', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   } else {
      $missing++;
      echo $prog.' mancante'.PHP_EOL;
   }
   sleep(1);
   $prog++;
} 


