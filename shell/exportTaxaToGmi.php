<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
if (!class_exists('\Autoload')) {
   require __DIR__.'/../lib/core/Autoload.php';
   \Autoload::getInstance();
}
if (!class_exists('\floraexport\Autoload')) {
   require __DIR__.'/../lib/floraexport/Autoload.php';
   \floraexport\Autoload::getInstance();
}


$mysql = $GLOBALS['db']->getDriver()->getConnection()->getResource();
$baseDir = __DIR__.'/../gmi';
if (!is_dir($baseDir)) {
   mkdir($baseDir);
}
$id = 1;
$gmiParser = new \floraexport\GmiExport($baseDir,$id,fopen('php://stdout', 'wb'));
$gmiParser->parse();
