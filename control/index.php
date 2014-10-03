<?php
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
$dico = new \flora\dico\Dico($GLOBALS['db']);
if (array_key_exists('id', $_REQUEST)) {
   $taxa->loadFromId($_REQUEST['id']);
   $dico = $taxa->getDico();
} else {
   $dico->loadRoot();
}
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
   case 'taxasearch':
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $taxaColl->loadAll($_REQUEST);
      $result = array();
      foreach ($taxaColl->getItems() as $taxa) {
         $result[] = array(
             'label'=>$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name'),
             'value'=>$GLOBALS['config']->baseUrl.'/index.php?id='.$taxa->getData('id')
         );
      } 
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
   break;
}
$taxa->dico = $dico;
$this->getTemplate()->setObjectData($taxa);
$this->getTemplate()->setBlock('middle','general/index.phtml');

