<?php
function noLimitDumpRestore($db,$filename) {

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
            $db->query($query
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
        $query = '';
        
        $queryCount++;
    }
}