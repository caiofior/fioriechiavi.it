<?php
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
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
        if ($message != '') {
            echo $message;
        }
        array_unshift($doneIds,$id);
        $shifts = 0;
    } else {
        array_push($todoIds, $id);
    }
} while ($shifts == 0 || $resultSet->valid());
foreach ($todoIds as $id ) {
    $taxa->loadFromId($id);
    echo 'Taxa '.$taxa->getData('name').' '.$taxa->getData('id').' has no parent'.PHP_EOL;
}
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