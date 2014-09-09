<?php
if (key_exists('sEcho', $_REQUEST)) {
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
               $data = '<a class="actions modify" href="?task=taxa_category&action=edit&id='.$taxaKind->getData('id').'">Modifica</a><a class="actions delete" href="?task=taxa_category&action=delete&id='.$taxaKind->getData('id').'">Cancella</a>';
            } 
            $row[] = $data;     
         }
         $result['aaData'][]=$row;
      }
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
}
if (!key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case 'edit':
   $this->getTemplate()->setBlock('middle','administrator/taxa_category/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml');  
   if (
            key_exists('xhrValidate', $_REQUEST) ||
            key_exists('submit', $_REQUEST)
      ) {
      if (!key_exists('initials', $_REQUEST) ||$_REQUEST['initials']=='') {
          $this->addValidationMessage('initials','Le iniziali sono obbligatorie');
      }
      if (!key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
          $this->addValidationMessage('name','Il nome è obbligatorio');
      }
      if (key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $taxaKind = new \flora\taxa\TaxaKind($GLOBALS['db']);
         if (key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxaKind->loadFromId($_REQUEST['id']);
         }
         $taxaKind->setData($_REQUEST);
         if (key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxaKind->update();
         } else {
            $taxaKind->insert();
         }
         $this->getTemplate()->setBlock('middle','administrator/taxa_category/list.phtml');
         $this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml'); 
      }
   }
   break; 
case 'delete' :
   $taxaKind = new \flora\taxa\TaxaKind($GLOBALS['db']);
   if (key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
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
   
   die();
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/taxa_category/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml');  
break;
}