<?php
if (key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $dicoColl = new \flora\dico\DicoColl($GLOBALS['db']);
      $dicoColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$dicoColl->countAll();
      $result['iTotalDisplayRecords']=$dicoColl->count();
      $result['aaData']=array();
      $columns = $dicoColl->getColumns();
      foreach($dicoColl->getItems() as $key => $dico) {
         $row=array();
         foreach($columns as $column) {
            $data = $dico->getRawData($column);
            if ($column == 'actions') {
               $data = '<a class="actions modify" href="?task=dico&amp;action=edit&amp;id='.$dico->getData('id').'">Modifica</a><a class="actions delete" href="?task=dico&amp;action=delete&amp;id='.$dico->getData('id').'">Cancella</a>';
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
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   $dico->insert();
   exit;
   break; 
case 'delete' :
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   if (key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $dico->loadFromId($_REQUEST['id']);
      $dico->delete();
   }
   exit;
   break;
case 'sort' :
   $this->getTemplate()->setBlock('middle','administrator/dico/sort.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');     
break;
default:
   $this->getTemplate()->setBlock('middle','administrator/dico/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');  
break;
}