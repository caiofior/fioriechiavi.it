<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $taxaKindColl = new \flora\taxa\TaxaKindColl($GLOBALS['db']);
      $taxaKindColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$taxaKindColl->countAll();
      $result['iTotalDisplayRecords']=$taxaKindColl->count();
      $result['aaData']=array();
      $columns = $taxaKindColl->getColumns();
      foreach($taxaKindColl->getItems() as $key => $taxaKind) {
         $row=array();
         foreach($columns as $column) {
            $data = $taxaKind->getRawData($column);
            if ($column == 'actions') {
               $data = '<a class="actions modify" title="Modifica" href="?task=taxa_category&amp;action=edit&amp;id='.$taxaKind->getData('id').'">Modifica</a><a class="actions delete" title="Cancella" href="?task=taxa_category&amp;action=delete&amp;id='.$taxaKind->getData('id').'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/taxa_category/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
      if (!array_key_exists('initials', $_REQUEST) ||$_REQUEST['initials']=='') {
          $this->addValidationMessage('initials','Le iniziali sono obbligatorie');
      }
      if (!array_key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
          $this->addValidationMessage('name','Il nome è obbligatorio');
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $taxaKind = new \flora\taxa\TaxaKind($GLOBALS['db']);
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxaKind->loadFromId($_REQUEST['id']);
         }
         $taxaKind->setData($_REQUEST);
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxaKind->update();
         } else {
            $taxaKind->insert();
         }
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa_category');
         exit(); 
      }
   }
   break; 
case 'delete' :
   $taxaKind = new \flora\taxa\TaxaKind($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $taxaKind->loadFromId($_REQUEST['id']);
      $taxaKind->delete();
   }
   exit;
   break;
case 'sort' :
   $this->getTemplate()->setBlock('middle','administrator/taxa_category/sort.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml');     
break;
case 'save_sort' :
   $taxaKind = new \flora\taxa\TaxaKind($GLOBALS['db']);
   foreach ($_REQUEST as $order=>$id) {
      if (!is_numeric($order)) continue;
      $id = substr($id, 1);
       $taxaKind->loadFromId($id);
       $taxaKind->setData($order+1, 'ord');
       $taxaKind->update();
   }
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/taxa_category/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml');  
break;
}