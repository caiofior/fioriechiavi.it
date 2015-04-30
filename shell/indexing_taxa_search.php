<?php
require __DIR__.'/../include/pageboot.php';
$statement = $GLOBALS['db']->query('TRUNCATE TABLE `taxa_search`');
$statement->execute();
$statement = $GLOBALS['db']->query('SELECT `id` FROM `taxa` ORDER BY `id` ASC');
$resultSet = new \Zend\Db\ResultSet\ResultSet();
$resultSet->initialize($statement->execute());
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
do {
    $id = $resultSet->current()->id;
    $taxa->loadFromId($id);
    try {
    $taxa->updateSearch();
    } catch (\Exception $e) {
        switch($e->getCode()) {
            case 3004409 :
                echo $e->getMessage().PHP_EOL;
            break;
            default:
                throw $e;
            break;
        }
    }
    $resultSet->next();
} while ($resultSet->valid());


