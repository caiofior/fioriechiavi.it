<?php
require __DIR__.'/../lib/florasearch/Autoload.php';
florasearch\Autoload::getInstance();
$floraSearch = new \florasearch\Search($GLOBALS['db']);
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
if (!array_key_exists('start', $_REQUEST)) {
    $_REQUEST['start']=0;
}
if (!array_key_exists('pagelength', $_REQUEST)) {
    $_REQUEST['pagelength']=10;
}
if (
    !array_key_exists('region', $_REQUEST)||
    !is_array($_REQUEST['region'])
   ) {
    $_REQUEST['region']=$floraSearch->getRegionColl()->getFieldsAsArray('id');
}
if (
    !array_key_exists('altitude', $_REQUEST)||
    !is_array($_REQUEST['altitude'])
   ) {
    $_REQUEST['altitude']=$floraSearch->getAltitudeArray();
}
$floraSearch->setRequest($_REQUEST);
switch ($_REQUEST['action']) {
    case 'autocomplete':
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $_REQUEST['iDisplayStart']=0;
      $_REQUEST['iDisplayLength']=10;
      $taxaColl->loadAll($_REQUEST);
      $result = array();
      foreach ($taxaColl->getItems() as $taxa) {
          $result[] = $taxa->getData('name');     
      } 
      header('Content-Type: application/json');
      echo json_encode($result);
   exit;
   case 'search' :
      $result = array();
      ob_start();
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'regionFilter.phtml';
      $result['regionFilter']=  ob_get_clean();
      ob_start();
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'searchContent.phtml';
      $result['searchContent']=  ob_get_clean();
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
   break;
}
$this->getTemplate()->setObjectData($floraSearch);
$this->getTemplate()->setBlock('middle','search/middle.phtml');
$this->getTemplate()->setBlock('footer','search/footer.phtml');

