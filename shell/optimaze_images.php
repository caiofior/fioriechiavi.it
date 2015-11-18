<?php
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
echo 'Searching images '.date('d/m/Y H:i:s').PHP_EOL;
$images = array();
$imagesBaseDir = $GLOBALS['db']->baseDir.'images'.DIRECTORY_SEPARATOR.'taxa';
$imagesIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesBaseDir,FilesystemIterator::SKIP_DOTS));
foreach($imagesIterator as $image) {
   echo exec('optipng '.$image->getRealPath());
}

echo 'Memory '.  number_format(memory_get_peak_usage()/1024/1024,2).' M'.PHP_EOL;
echo 'End at '.date('d/m/Y H:i:s').PHP_EOL;