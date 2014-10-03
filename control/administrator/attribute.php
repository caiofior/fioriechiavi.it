<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
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
               $data = '<a class="actions delete" title="Cancella" href="?task=attribute&amp;action=delete&amp;id='.$attribute->getData('id').'">Cancella</a>';
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
case 'delete' :
   $attribute = new \flora\taxa\TaxaAttribute($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {      
      $attribute->loadFromId($_REQUEST['id']);
      $attribute->delete();
   }
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/attribute/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/attribute/footer.phtml');  
break;
}