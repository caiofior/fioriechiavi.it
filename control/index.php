<?php
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
if (array_key_exists('id', $_REQUEST)) {
   $taxa->loadFromId($_REQUEST['id']);
} else {
   $taxa->loadRoot();
}
if (array_key_exists('id', $_REQUEST) && $taxa->getRawData('status') == 0) {
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $GLOBALS['config']->baseUrl.'/undefined_location.html');
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($handle);
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); 
    echo $response;
    exit;
}
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
   case 'signalObservation':
       require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'general'.DIRECTORY_SEPARATOR.'signalObservation.phtml';
   exit; 
   case 'taxasearch':
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $_REQUEST['iDisplayStart']=0;
      $_REQUEST['iDisplayLength']=10;
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
$this->getTemplate()->setObjectData($taxa);
$this->getTemplate()->setBlock('middle','general/index.phtml');

