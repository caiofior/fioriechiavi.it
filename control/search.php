<?php
require __DIR__.'/../lib/florasearch/Autoload.php';
florasearch\Autoload::getInstance();
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
if (!array_key_exists('start', $_REQUEST)) {
    $_REQUEST['start']=0;
}
if (!array_key_exists('pagelength', $_REQUEST)) {
    $_REQUEST['pagelength']=10;
}
$floraSearch = new \florasearch\Search($GLOBALS['db'],$_REQUEST);
switch ($_REQUEST['action']) {
   case 'search' :
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'searchContent.phtml';
      exit;
   break;
}
$this->getTemplate()->setObjectData($floraSearch);
$this->getTemplate()->setBlock('middle','search/middle.phtml');
$this->getTemplate()->setBlock('footer','search/footer.phtml');

