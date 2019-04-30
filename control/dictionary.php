<?php
$this->getTemplate()->setBlock('middle','dictionary/detail.phtml');
$term = new \dictionary\Term($GLOBALS['db']);
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
if (!array_key_exists('start', $_REQUEST)) {
    $_REQUEST['start']=0;
}
if (!array_key_exists('pagelength', $_REQUEST)) {
    $_REQUEST['pagelength']=10;
}
if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {
    $term->loadFromId($_REQUEST['id']);
    if($term->getData('id') =='') {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $GLOBALS['config']->baseUrl.'/undefined_location.html');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); 
        echo $response;
        exit;
    }
    $this->getTemplate()->setObjectData($term);
}
if ($term->getData('id') == '') {
    $termColl = new \dictionary\TermColl($GLOBALS['db']);
    $termColl->loadAll(array(
        'iDisplayStart'=>$_REQUEST['start'],
        'iDisplayLength'=>$_REQUEST['pagelength']
    ));
    $this->getTemplate()->setObjectData($termColl);
    $this->getTemplate()->setBlock('middle','dictionary/middle.phtml');
}
if (array_key_exists('xhr',$_REQUEST) && $_REQUEST['xhr'] == 1) {
   require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'dictionary'.DIRECTORY_SEPARATOR.'middleContent.phtml';
   exit;
}
$this->getTemplate()->setBlock('head','dictionary/head.phtml');
$this->getTemplate()->setBlock('footer','dictionary/footer.phtml');

