<?php
if (array_key_exists('sEcho', $_REQUEST)) {
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
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case 'edit':
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $this->getTemplate()->setObjectData($dico);
   $this->getTemplate()->setBlock('middle','administrator/dico/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');  
   break; 
case 'delete' :
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $dico->loadFromId($_REQUEST['id']);
      $dico->delete();
   }
   exit;
   break;
case 'jeditable' :
   if (
           array_key_exists('id_dico',$_REQUEST) &&
           is_numeric($_REQUEST['id_dico']) &&
           array_key_exists('id',$_REQUEST) &&
           strlen($_REQUEST['id']) > 1 &&
           is_numeric(substr($_REQUEST['id'],1))
       ) {
         $dico = new \flora\dico\Dico($GLOBALS['db']);
         $dico->loadFromId($_REQUEST['id_dico']);
         echo $dico->setDicoItemValue(substr($_REQUEST['id'],1),$_REQUEST['value']);
         exit;
       }
   break;
case 'taxalist':
   $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
   $taxaColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaColl->getItems() as $taxa) {
      $result[] = array(
          'label'=>$taxa->getData('name'),
          'value'=>$taxa->getData('id')
      );
   } 
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/dico/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');  
break;
}