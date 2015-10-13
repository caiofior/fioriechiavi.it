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
      header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
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
      header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
      echo json_encode($result);
      exit;
   break;
}
$this->getTemplate()->setObjectData($floraSearch);
$this->getTemplate()->setBlock('head','search/head.phtml');
$this->getTemplate()->setBlock('middle','search/middle.phtml');
$this->getTemplate()->setBlock('footer','search/footer.phtml');

