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
if (
    !array_key_exists('flowering', $_REQUEST)||
    !is_array($_REQUEST['flowering'])
   ) {
    $_REQUEST['flowering']=$floraSearch->getFloweringArray();
}
if (
    !array_key_exists('posture', $_REQUEST)||
    !is_array($_REQUEST['posture'])
   ) {
    $_REQUEST['posture']=$floraSearch->getPostureArray();
}
if (
    !array_key_exists('biologicForm', $_REQUEST)||
    !is_array($_REQUEST['biologicForm'])
   ) {
    $_REQUEST['biologicForm']=$floraSearch->getBiologicFormArray();
}
if (
    !array_key_exists('community', $_REQUEST)||
    !is_array($_REQUEST['community'])
   ) {
    $_REQUEST['community']=$floraSearch->getCommunityArray();
}
$floraSearch->setRequest($_REQUEST);
switch ($_REQUEST['action']) {
    case 'autocomplete':
      $taxaColl = $floraSearch->getTaxaParentColl();
      $result = array();
      foreach ($taxaColl->getItems() as $taxa) {
         $result[] = array(
             'label'=>$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name').' ('.$taxa->getRawData('count').')',
             'value'=>$taxa->getData('id')
         );
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
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'altitudeFilter.phtml';
      $result['altitudeFilter']=  ob_get_clean();
      ob_start();
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'floweringFilter.phtml';
      $result['floweringFilter']=  ob_get_clean();
      ob_start();
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'postureFilter.phtml';
      $result['postureFilter']=  ob_get_clean();
      ob_start();
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'biologicFormFilter.phtml';
      $result['biologicFormFilter']=  ob_get_clean();
      ob_start();
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'searchContent.phtml';
      $result['searchContent']=  ob_get_clean();
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
   break;
}
$this->getTemplate()->setObjectData($floraSearch);
$this->getTemplate()->setBlock('head','search/head.phtml');
$this->getTemplate()->setBlock('middle','search/middle.phtml');
$this->getTemplate()->setBlock('footer','search/footer.phtml');

