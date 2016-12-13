<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      if (!array_key_exists('action', $_REQUEST)) {
         $_REQUEST['action']=null;
      }
      switch ($_REQUEST['action']) {
      default:
         $linkProviderColl = new \flora\linkprovider\LinkProviderColl($GLOBALS['db']);
         $linkProviderColl->loadAll($_REQUEST);
         $result['sEcho']=intval($_REQUEST['sEcho']);
         $request = $_REQUEST;
         unset($request['sSearch']);
         $result['iTotalRecords']=$linkProviderColl->countAll($request);
         $result['iTotalDisplayRecords']=$linkProviderColl->countAll($_REQUEST);
         $result['aaData']=array();
         $columns = $linkProviderColl->getColumns();
         foreach($linkProviderColl->getItems() as $key => $linkProvider) {
            $row=array();
            foreach($columns as $column) {
               $data = $linkProvider->getRawData($column);
               if ($column == 'actions') {
                  $data = '<a class="actions edit" title="Modifica" href="?task=link&amp;action=edit&amp;id='.$linkProvider->getData('id').'">Modifica</a>';
                  $data .= '<a class="actions delete" title="Cancella" href="?task=link&amp;action=delete&amp;id='.$linkProvider->getData('id').'">Cancella</a>';
               } 
               $row[] = $data;     
            }
            $result['aaData'][]=$row;
         }
      break;
      }
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
}
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case 'edit' :
   $this->getTemplate()->setBlock('middle','administrator/link/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/link/footer.phtml');  
	  if (
			array_key_exists('xhrValidate', $_REQUEST) ||
			array_key_exists('submit', $_REQUEST)
	  ) {
	  $linkProvider = new flora\linkprovider\LinkProvider($GLOBALS['db']);	  
	  if (!array_key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
		  $this->addValidationMessage('name','Il nome è obbligatorio');
	  } else {
		  $linkProvider->loadFromName($_REQUEST['name']);
		  if (
				(
					!array_key_exists('id', $_REQUEST) ||
					!is_numeric($_REQUEST['id'])
				)
				&& $linkProvider->getData('name') == $_REQUEST['name']
			) {
			$this->addValidationMessage('name','Provider già registrato');  
		  }
	  }
	  
	  if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
		 
		 if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
			$linkProvider->loadFromId($_REQUEST['id']);
		 }
		 $linkProvider->setData($_REQUEST);
		 if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
			$linkProvider->update();
		 } else {
			$linkProvider->insert();
		 }
		 header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=link&action=edit&id='.$linkProvider->getData('id'));
		 exit(); 
	  }
   }
   break;
case 'delete' :
   $attribute = new \flora\taxa\TaxaAttribute($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {      
      $attribute->loadFromId($_REQUEST['id']);
      $attribute->delete();
   }
   exit;
   break;
case 'deletevalue' :
   $attribute = new \flora\taxa\TaxaAttribute($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {      
      $attribute->loadFromId($_REQUEST['id']);
      $attribute->deleteValue($_REQUEST['value']);
   }
   exit;
   break;
case 'jeditable' :
   $attribute = new \flora\taxa\TaxaAttribute($GLOBALS['db']);
   if (array_key_exists('id', $_GET) && $_GET['id'] != '') {      
      $attribute->loadFromId($_GET['id']);
      echo $attribute->replaceValue($_REQUEST['old_val'],$_REQUEST['value']);
   }
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/link/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/link/footer.phtml');  
break;
}
