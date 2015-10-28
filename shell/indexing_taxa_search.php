<?php
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
if  (
        $GLOBALS['db']->config->background->useAJAX != true &&
        is_numeric($GLOBALS['db']->config->background->splitEvery) &&
        isset($argv) &&
        is_array($argv) &&
        !array_key_exists(1, $argv)
    ) {
    $statement = $GLOBALS['db']->query('SELECT COUNT(`id`) FROM `taxa`');
    $resultSet = new \Zend\Db\ResultSet\ResultSet();
    $resultSet->initialize($statement->execute());    
    $count = $resultSet->current()['COUNT(`id`)'];
    $steps = range(0,$count,intval($GLOBALS['db']->config->indexing->splitEvery));
    foreach ($steps as $step) {
        echo 'Step '.$step.PHP_EOL;
        $cmd = 'php '.__FILE__.' '.$step.' 2>&1';
        echo shell_exec($cmd);
    }
    echo 'Memory '.  number_format(memory_get_peak_usage()/1024/1024,2).' M'.PHP_EOL;
    echo 'End at '.date('d/m/Y H:i:s').PHP_EOL;
    exit;
}
if (
        !isset($argv) ||
        !is_array($argv) ||
        !array_key_exists(1, $argv) ||
        $argv[1]==0
        ) {
    echo 'Cleaned up search table '.date('d/m/Y H:i:s').PHP_EOL;
    $statement = $GLOBALS['db']->query('TRUNCATE TABLE `taxa_search`');
    $statement->execute();
}
if  (
        is_numeric($GLOBALS['db']->config->background->splitEvery) &&
        isset($argv) &&
        is_array($argv) &&
        array_key_exists(1, $argv)
    ) {
    $statement = $GLOBALS['db']->query('
        SELECT `id` ,
        (SELECT `dico_item`.`parent_taxa_id` FROM `dico_item` WHERE `dico_item`.`taxa_id` = `taxa`.`id` ORDER BY `dico_item`.`parent_taxa_id` DESC LIMIT 1) as `parent_taxa_id`
        FROM `taxa` ORDER BY `id` ASC LIMIT '.intval($argv[1]).','.intval($GLOBALS['db']->config->indexing->splitEvery));
} else {
    $statement = $GLOBALS['db']->query('
        SELECT `id` ,
        (SELECT `dico_item`.`parent_taxa_id` FROM `dico_item` WHERE `dico_item`.`taxa_id` = `taxa`.`id` ORDER BY `dico_item`.`parent_taxa_id` DESC  LIMIT 1) as `parent_taxa_id`
        FROM `taxa` ORDER BY `id` ASC');
}
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
        $parentIds[$id]=$resultSet->current()->parent_taxa_id;
        array_unshift($todoIds,$id);
        $resultSet->next();
    }
    $id = array_shift($todoIds);
    $shifts =0;
       
    while (
            $id > 1 &&
            !in_array($parentIds[$id],$doneIds) &&
            $shifts++ < count($todoIds)
          ) {
    
        array_push($todoIds, $id);
        $id = array_shift($todoIds);
    }
    
    if (
            $id == 1 ||
            in_array($parentIds[$id],$doneIds)
        ) {
        
        $taxa->loadFromId($id);
        $message = $taxa->updateSearch();
        if ($message != '') {
            echo $message;
        }
        array_unshift($doneIds,$id);
        
    }
    
} while (count($todoIds)>0 || $resultSet->valid());

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