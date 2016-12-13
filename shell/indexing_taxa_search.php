<?php
register_shutdown_function('server_resource_monitoring');
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
echo 'Searching images '.date('d/m/Y H:i:s').PHP_EOL;
$images = array();
$imagesBaseDir = $GLOBALS['db']->baseDir.'images'.DIRECTORY_SEPARATOR.'taxa';
$imagesIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesBaseDir,FilesystemIterator::SKIP_DOTS));
foreach($imagesIterator as $image) {
    $images[] = str_replace($imagesBaseDir,'',$image->getRealPath());
}

echo 'Cleaned up search table '.date('d/m/Y H:i:s').PHP_EOL;
$statement = $GLOBALS['db']->query('TRUNCATE TABLE `taxa_search`');
$statement->execute();

$statement = $GLOBALS['db']->query('
    SELECT `id`
    FROM `taxa` ORDER BY `id` ASC');

$resultSet = new \Zend\Db\ResultSet\ResultSet();
$resultSet->initialize($statement->execute());

$taxa = new \flora\taxa\Taxa($GLOBALS['db']);

$statement = $GLOBALS['db']->query('ALTER TABLE `taxa_search` DISABLE KEYS');
$statement->execute();
$statement = $GLOBALS['db']->query('ALTER TABLE `taxa_search_attribute` DISABLE KEYS');
$statement->execute();

$doneIds=array();
$parentIds=array();
$todoIds =array();
do {
    unset($id);
    if ($resultSet->valid()) {
        $id = $resultSet->current()->id;
        
        
        $parentIds[$id]=array();
        $statement = $GLOBALS['db']->query('
            SELECT `parent_taxa_id` FROM `dico_item` WHERE `taxa_id` = '.$id.' ORDER BY `dico_item`.`parent_taxa_id` DESC
        ');
        $parentResultSet = new \Zend\Db\ResultSet\ResultSet();
        $parentResultSet->initialize($statement->execute());
        while($parentResultSet->valid()) {
            $parentIds[$id][]=$parentResultSet->current()->parent_taxa_id;
            $parentResultSet->next();
        }
        array_unshift($todoIds,$id);
        $resultSet->next();
    }
    $id = array_shift($todoIds);
    $shifts = 0;
    while (
            $id > 1 &&
            count(array_intersect($parentIds[$id],$doneIds)) == 0 &&
            $shifts++ <= count($todoIds)
          ) {
        array_push($todoIds, $id);
        $id = array_shift($todoIds);
    }
    $taxa->loadFromId($id);
    if (
            $id == 1 ||
            count(array_intersect($parentIds[$id],$doneIds)) > 0
        ) {
        $message = $taxa->updateSearch();
        $imagesColl = $taxa->getTaxaImageColl();
        $imagesNames = $imagesColl->getFieldsAsArray('filename');
        $images = array_diff($images,$imagesNames);
        if ($message != '') {
            echo $message;
        }
        array_unshift($doneIds,$id);
        $shifts = 0;
    } else {
        array_push($todoIds, $id);
    }
    if(php_sapi_name() != 'cli') {
        echo str_repeat(' ', 50).PHP_EOL;
        flush();
        set_time_limit(30);
        if ($GLOBALS['db']->config->background->sleep != '') {
            usleep($GLOBALS['db']->config->background->sleep);
        }
    }
} while ($shifts == 0 || $resultSet->valid());
foreach ($todoIds as $id ) {
    $taxa->loadFromId($id);
    $imagesNames = $imagesColl->getFieldsAsArray('filename');
    $images = array_diff($images,$imagesNames);
    echo 'Taxa '.$taxa->getData('name').' '.$taxa->getData('id').' has no parent'.PHP_EOL;
    if(php_sapi_name() != 'cli') {
        echo str_repeat(' ', 50).PHP_EOL;
        ob_flush();
        set_time_limit(30);
        if ($GLOBALS['db']->config->background->sleep != '') {
            usleep($GLOBALS['db']->config->background->sleep);
        }
    }
}
if (count($images) > 0) {
    echo 'Deleted '.count($images).' unused images'.PHP_EOL;
}
foreach($images as $image) {
    unlink($imagesBaseDir.$image);
}
$statement = $GLOBALS['db']->query('
    DELETE FROM `taxa_image` WHERE `id` IN (
        SELECT * FROM
        (
            SELECT `id` FROM `taxa_image`
            WHERE (SELECT `id` FROM `taxa` WHERE `id`=`taxa_image`.`taxa_id`) IS NULL
        ) AS t
    )
');
$statement->execute();

$statement = $GLOBALS['db']->query('
    DELETE FROM `taxa_observation` WHERE
    `id` IN (
    SELECT * FROM
    (
        SELECT `id` FROM `taxa_observation`
        WHERE (SELECT `id` FROM `taxa` WHERE `id`=`taxa_observation`.`taxa_id`) IS NULL
    ) AS t
    )
');
$statement->execute();

$statement = $GLOBALS['db']->query('
    DELETE FROM `taxa_observation_image` WHERE
    `id` IN (
    SELECT * FROM
    (
        SELECT `id` FROM `taxa_observation_image`
        WHERE (SELECT `id` FROM `taxa_observation` WHERE `id`=`taxa_observation_image`.`taxa_observation_id`) IS NULL
    ) AS t
    );
');
$statement->execute();

$statement = $GLOBALS['db']->query('ALTER TABLE `taxa_search` ENABLE KEYS');
$statement->execute();
$statement = $GLOBALS['db']->query('ALTER TABLE `taxa_search_attribute` ENABLE KEYS');
$statement->execute();

$statement = $GLOBALS['db']->query('OPTIMIZE TABLE `taxa_search`');
$statement->execute();
$statement = $GLOBALS['db']->query('OPTIMIZE TABLE `taxa_search_attribute`');
$statement->execute();

echo 'Number of taxa '.count($doneIds).PHP_EOL;
$sql = '
SELECT 
ROUND(((data_length + index_length) / 1024 / 1024), 2) as size
FROM information_schema.TABLES 
WHERE table_schema = "'.$GLOBALS['db']->config->database->database.'"
 AND table_name = "taxa_search";
    ';
$statement = $GLOBALS['db']->query($sql);
$tableData = $statement->execute();
$tableData = $tableData->current();
echo 'Table size '.$tableData['size'].' Mb'.PHP_EOL;
$GLOBALS['db']->query('ALTER TABLE `taxa_search` ORDER BY `taxa_id` DESC'
, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
echo 'Memory '.  number_format(memory_get_peak_usage()/1024/1024,2).' M'.PHP_EOL;
echo 'End at '.date('d/m/Y H:i:s').PHP_EOL;

function server_resource_monitoring() {
    $error_codes = array(
        1=>'E_ERROR',
        2=>'E_WARNING',
        4=>'E_PARSE',
        8=>'E_NOTICE',   
        16=>'E_CORE_ERROR',
        32=>'E_CORE_WARNING',   
        64=>'E_COMPILE_ERROR',
        128=>'E_COMPILE_WARNING',   
        256=>'E_USER_ERROR',
        512=>'E_USER_WARNING',
        1024=>'E_USER_NOTICE',
        2048=>'E_STRICT',
        4096=>'E_RECOVERABLE_ERROR',
        8192=>'E_DEPRECATED',
        16384=>'E_USER_DEPRECATED',
        32767=>'E_ALL'
    );
    $error = error_get_last();
    $error_message = '';
    if ($error['type'] != 2 &&
            $error['type'] != 8 &&
            $error['type'] != 32 &&
            $error['type'] != 128 &&
            $error['type'] != 512 &&
            $error['type'] != 1024 &&
            $error['type'] != 2048 &&
            $error['type'] != 8192 &&
            $error['type'] != 16384 &&
            $error['type'] != '') {
        $error["type"] = $error_codes[$error["type"]];
        
        echo "Tipo\t" . $error["type"] . PHP_EOL;
        echo "Messaggio\t" . $error["message"] . PHP_EOL;
        echo "File\t" . $error["file"] . PHP_EOL;
        echo "Linea\t" . $error["line"] . PHP_EOL;
        
    }
}