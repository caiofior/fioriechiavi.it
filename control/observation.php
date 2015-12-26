<?php
$this->getTemplate()->setBlock('middle','observation/detail.phtml');
$taxaObservation = new \floraobservation\TaxaObservation($GLOBALS['db']);
if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {
    $taxaObservation->loadFromId($_REQUEST['id']);
    if(!$taxaObservation->getData('valid')) {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $GLOBALS['config']->baseUrl.'/undefined_location.html');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); 
        echo $response;
        exit;
    }
    $this->getTemplate()->setObjectData($taxaObservation);
}
if ($taxaObservation->getData('id') == '') {
    $taxaObservationColl = new \floraobservation\TaxaObservationColl($GLOBALS['db']);
    $taxaObservationColl->loadAll(array(
        'iDisplayStart'=>0,
        'iDisplayLength'=>10,
        'sColumns'=>'datetime',
        'iSortingCols'=>'1',
        'iSortCol_0'=>'0',
        'sSortDir_0'=>'DESC',
	'valid'=>true
    ));
    $this->getTemplate()->setObjectData($taxaObservationColl);
    $this->getTemplate()->setBlock('middle','observation/middle.phtml');
}
$this->getTemplate()->setBlock('head','observation/head.phtml');
$this->getTemplate()->setBlock('footer','observation/footer.phtml');

