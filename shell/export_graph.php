<?php
echo 'Start at '.date('d/m/Y H:i:s').PHP_EOL;
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$fName = 'php://stdout';
if (is_array($argv) && array_key_exists('1', $argv)) {
    $fName = $argv[1];    
}
$fh = fopen ($fName, 'w');
$statement = $GLOBALS['db']->query('
    SELECT `taxa_id`,`parent_taxa_id`
    FROM `dico_item` 
    WHERE `parent_taxa_id` <> "" AND `taxa_id` <> ""
    ORDER BY `parent_taxa_id` ASC');
$resultSet = new \Zend\Db\ResultSet\ResultSet();
$resultSet->initialize($statement->execute());
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
$parentTaxa = new \flora\taxa\Taxa($GLOBALS['db']);
do {
    $taxa->loadFromId($resultSet->current()->taxa_id);
    $parentTaxa->loadFromId($resultSet->current()->parent_taxa_id);
    fputcsv($fh, array(
        'taxa'=>$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name'),
        'parentTaxa'=>$parentTaxa->getRawData('taxa_kind_initials').' '.$parentTaxa->getData('name')
    ));
    $resultSet->next();
} while ($resultSet->valid());
fclose($fh);
echo 'Memory '.  number_format(memory_get_peak_usage()/1024/1024,2).' M'.PHP_EOL;
echo 'End at '.date('d/m/Y H:i:s').PHP_EOL;