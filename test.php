<?php
require __DIR__.'/include/pageboot.php';
require $GLOBALS['db']->baseDir.'/lib/noLimitDumpRestore/noLimitDumpRestore.php';
$filePath = '/tmp/backup_taxa_2016-06-27.sql';
noLimitDumpRestore($GLOBALS['db'],$filePath);
   
