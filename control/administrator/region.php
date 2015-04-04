<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $regionColl = new \flora\region\RegionColl($GLOBALS['db']);
      $regionColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$regionColl->countAll($request);
      $result['iTotalDisplayRecords']=$regionColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $regionColl->getColumns();
      foreach($regionColl->getItems() as $key => $region) {
         $row=array();
         foreach($columns as $column) {
            $data = $region->getRawData($column);
            if ($column == 'actions') {
               $data = '<a class="actions delete" title="Cancella" href="?task=region&amp;action=delete&amp;id='.$region->getData('id').'">Cancella</a>';
            } 
            $row[] = $data;     
         }
         $result['aaData'][]=$row;
      }
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
}
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case 'edit':
   $this->getTemplate()->setBlock('middle','administrator/region/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/region/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
      if (!array_key_exists('id', $_REQUEST) ||$_REQUEST['id']=='') {
          $this->addValidationMessage('id','il codice è obbligatorio');
      }
      if (!array_key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
          $this->addValidationMessage('name','Il nome è obbligatorio');
      }
      $region = new \flora\region\Region($GLOBALS['db']);
      $region->loadFromId($_REQUEST['id']);
      if ($region->getData('id') != '') {
         $this->addValidationMessage('id','codice già presente');
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $region = new \flora\region\Region($GLOBALS['db']);
         $region->setData($_REQUEST);
         $region->insert();
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=region');
         exit(); 
      }
   }
   break; 
case 'delete' :
   $region = new \flora\region\Region($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {      
      $region->loadFromId($_REQUEST['id']);
      $region->delete();
   }
   exit;
   break;
case 'jeditable' :
   if (
           array_key_exists('id',$_REQUEST) &&
           is_numeric($_REQUEST['id']) &&
           array_key_exists('value',$_REQUEST) &&
           strlen($_REQUEST['value']) > 1
       ) {
         $region = new \flora\region\Region($GLOBALS['db']);
         $region->loadFromId($_REQUEST['id']);
         $region->setData($_REQUEST['value'], 'name');
         $region->update();
         $region->loadFromId($_REQUEST['id']);
         echo $region->getData('name');
         exit;
       }
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/region/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/region/footer.phtml');  
break;
}