<?php
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;


require __DIR__.'/../include/pageboot.php';

$statement = $GLOBALS['db']->query('TRUNCATE TABLE `taxa_search`');
$statement->execute();
$statement = $GLOBALS['db']->query('SELECT `id` FROM `taxa` ORDER BY `id` ASC');
$resultSet = new \Zend\Db\ResultSet\ResultSet();
$resultSet->initialize($statement->execute());
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
$c=0;
do {
    $c++;
    $id = $resultSet->current()->id;
    $taxa->loadFromId($id);
    $message = $taxa->updateSearch();
    if ($message != '') {
        echo $message.PHP_EOL;
    }
    $resultSet->next();
} while ($resultSet->valid());
echo 'Number of taxa '.$c.PHP_EOL;
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
echo 'End at '.date('d/m/Y H:i:s').PHP_EOL;