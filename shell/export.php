<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
require __DIR__.'/../lib/floraexport/Autoload.php';
\floraexport\Autoload::getInstance();
$rootId = 1;
$format = \floraexport\TaxaExport::MD;
$fileName = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'export_'.$rootId.'.'.$format;
if(is_array($argv) && key_exists(1, $argv)) {
    $rootId = max(intval($argv[1]),$rootId);
}
if(is_array($argv) && key_exists(2, $argv) && $argv[2] != '') {
    $format = $argv[2];
}
if(is_array($argv) && key_exists(3, $argv) && $argv[3] != '') {
    $fileName = $argv[3].DIRECTORY_SEPARATOR.'export_'.$rootId.'.'.$format;
}
$stream = fopen($fileName, 'w');
$taxaExport = new \floraexport\TaxaExport($GLOBALS['db'], $rootId);
$taxaExport->export($stream,$format);
fclose($stream);

