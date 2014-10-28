<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      if (!array_key_exists('action', $_REQUEST)) {
         $_REQUEST['action']=null;
      }
      switch ($_REQUEST['action']) {
      case 'value' :
         $attribute = new \flora\taxa\TaxaAttribute($GLOBALS['db']);
         $attribute->loadFromId($_REQUEST['id']);
         $values = $attribute->getAllValues($_REQUEST);
         $result['sEcho']=intval($_REQUEST['sEcho']);
         $result['iTotalRecords']=sizeof($values);
         $result['iTotalDisplayRecords']=sizeof($values);
         $result['aaData']=array();
         foreach($values as $value) {
            $result['aaData'][]=array(
                $value['value'],
                '<a class="actions edit" title="Modifica" #">Modifica</a>'.
                '<a class="actions delete" title="Cancella" href="?task=attribute&amp;action=deletevalue&amp;id='.intval($attribute->getData('id')).'&amp;value='.urlencode($value['value']).'">Cancella</a>'
            );
         }
      break;
      default:
         $attributeColl = new \flora\taxa\TaxaAttributeColl($GLOBALS['db']);
         $attributeColl->loadAll($_REQUEST);
         $result['sEcho']=intval($_REQUEST['sEcho']);
         $result['iTotalRecords']=$attributeColl->count();
         $result['iTotalDisplayRecords']=$attributeColl->countAll();
         $result['aaData']=array();
         $columns = $attributeColl->getColumns();
         foreach($attributeColl->getItems() as $key => $attribute) {
            $row=array();
            foreach($columns as $column) {
               $data = $attribute->getRawData($column);
               if ($column == 'actions') {
                  $data = '<a class="actions edit" title="Modifica" href="?task=attribute&amp;action=edit&amp;id='.$attribute->getData('id').'">Modifica</a>';
                  $data .= '<a class="actions delete" title="Cancella" href="?task=attribute&amp;action=delete&amp;id='.$attribute->getData('id').'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/attribute/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/attribute/footer.phtml');  
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
   $this->getTemplate()->setBlock('middle','administrator/attribute/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/attribute/footer.phtml');  
break;
}