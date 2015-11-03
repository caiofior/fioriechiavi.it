<?php
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$taxaSearch = new \flora\taxa\TaxaSearch($GLOBALS['db']);
$taxaSearch->generateSitemap();
echo 'Memory '.  number_format(memory_get_peak_usage()/1024/1024,2).' M'.PHP_EOL;
echo 'End at '.date('d/m/Y H:i:s').PHP_EOL;