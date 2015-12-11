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
   case 'Esporta':
   case 'export':
       $separator = ',';
       if (array_key_exists('separator', $_REQUEST)) {
           $separator = chr($_REQUEST['separator']);
       }
       $filename='taxa';
       if (array_key_exists('taxasearch',$_REQUEST) && $_REQUEST['taxasearch'] != '') {
        $filename = $_REQUEST['taxasearch'];
       }
       header('Content-Encoding: UTF-8');
       header('Content-Type: application/vnd.ms-excel');
       header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
       header('Cache-Control: max-age=0');
       $out = fopen('php://output', 'w');
       fwrite($out, "\xEF\xBB\xBF");
       if ($GLOBALS['profile'] instanceof \login\user\Profile) {
        unset($_REQUEST['start']);
        unset($_REQUEST['pagelength']);
        $floraSearch->setRequest($_REQUEST);
       }
       
       $taxaColl = $floraSearch->getTaxaColl();
       $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
       $taxaAttributeColl = new \flora\taxa\TaxaAttributeColl($GLOBALS['db']);
       $taxaAttributeColl->loadAll();
       $attributeNames = $taxaAttributeColl->getFieldsAsArray('name');
       foreach($taxaColl->getItems() as $c => $taxaSearch) {
           $taxa->loadFromId($taxaSearch->getData('id'));
           $data=array(
               'id'=>$taxaSearch->getData('id'),
               'name'=>$taxaSearch->getData('name'),
               'taxa_kind_id_name'=>$taxa->getData('taxa_kind_id_name'),
               'description'=>$taxa->getData('description'),
               'col_id'=>$taxa->getData('col_id'),
               'eol_id'=>$taxa->getData('eol_id'),
           );
           $taxaAttributeColl = $taxa->getTaxaAttributeColl();
           $rawAttributeValues = array();
           foreach($taxaAttributeColl->getItems() as $taxaAttribute) {
               $rawAttributeValues[$taxaAttribute->getData('name')]=$taxaAttribute->getRawData('value');
           }
           $attributeValues=array_flip($attributeNames);
           foreach($attributeValues as $name=>$value) {
               if (array_key_exists($name, $rawAttributeValues)) {
                   $data[$name]=$rawAttributeValues[$name];
               } else {
                   $data[$name]='';
               }
           }
           array_walk($data, create_function('&$value,$key','
               $value=str_replace("\"","\'",$value);
               '));
           if ($c == 0) {
               echo fputcsv($out,array_keys($data),$separator);    
           }
           echo fputcsv($out,$data,$separator);
       }
       fclose($out);
       exit;
       breaK;
}
$this->getTemplate()->setObjectData($floraSearch);
$this->getTemplate()->setBlock('head','search/head.phtml');
$this->getTemplate()->setBlock('middle','search/middle.phtml');
$this->getTemplate()->setBlock('footer','search/footer.phtml');

