<?php
function noLimitDumpRestore($db,$filename) {
if (!is_file($filename)) {
   throw new \Exception('File is not avaliable '.$filename);
}
if (
        is_callable('shell_exec')
   ) {
   $cmd = 'mysql';
   if ($GLOBALS['config']->database->hostname != '') {
      $cmd .= ' -h '.$GLOBALS['config']->database->hostname;
   }
   if ($GLOBALS['config']->database->username != '') {
      $cmd .= ' -u '.$GLOBALS['config']->database->username;
   }
   if ($GLOBALS['config']->database->password != '') {
      $cmd .= ' -p'.$GLOBALS['config']->database->password;
   }
   if ($GLOBALS['config']->database->database != '') {
      $cmd .= ' '.$GLOBALS['config']->database->database;
   }
   $cmd .= ' < '.$filename;
   shell_exec($cmd);
   return;
}
   
$maxRuntime = 8;

$deadline = time()+$maxRuntime; 

$fp = fopen($filename, 'r');

$queryCount = 0;
$query = '';
while(
        $deadline>time() && 
        ($line=fgets($fp, 1024000)) 
    ){
    if(
         substr($line,0,2)=='--' ||
         trim($line)=='' ) {
        continue;
    }

    $query .= $line;
    if( substr(trim($query),-1)==';' ){
            try {
            $db->query($query
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            } catch (\Exception $e) {}
            $query = '';
            $queryCount++;
        }
    }
}