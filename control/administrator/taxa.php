<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $taxaColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$taxaColl->countAll();
      $result['iTotalDisplayRecords']=$taxaColl->count();
      $result['aaData']=array();
      $columns = $taxaColl->getColumns();
      foreach($taxaColl->getItems() as $key => $taxa) {
         $row=array();
         foreach($columns as $column) {
            $data = $taxa->getRawData($column);
            if ($column == 'taxa_kind_id') {
               $data=$taxa->getRawData('taxa_kind_initials');
            } else if ($column == 'actions') {
               $data = '<a class="actions modify" title="Modifica" href="?task=taxa&amp;action=edit&amp;id='.$taxa->getData('id').'">Modifica</a><a class="actions delete" title="Cancella" href="?task=taxa&amp;action=delete&amp;id='.$taxa->getData('id').'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/taxa/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST) ||
            array_key_exists('submit_create_key', $_REQUEST)
      ) {
      if (!array_key_exists('taxa_kind_id', $_REQUEST) ||$_REQUEST['taxa_kind_id']=='') {
          $this->addValidationMessage('taxa_kind_id','Il tipo di tassonomia è obbligatorio');
      }
      if (!array_key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
          $this->addValidationMessage('name','Il nome è obbligatorio');
      }
      if (
              (
              array_key_exists('submit', $_REQUEST) ||
              array_key_exists('submit_create_key', $_REQUEST) 
              ) && $this->formIsValid()) {
         if (array_key_exists('submit_create_key', $_REQUEST)) {
            $dico = new \flora\dico\Dico($GLOBALS['db']);
            $dico->insert();
            $_REQUEST['dico_id']=$dico->getData('id');
         }
         
         $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxa->loadFromId($_REQUEST['id']);
         }
         $taxa->setData($_REQUEST);
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxa->update();
         } else {
            $taxa->insert();
         }
         if (
                 array_key_exists('children_dico_item_id', $_REQUEST) && is_numeric($_REQUEST['children_dico_item_id']) &&
                 array_key_exists('children_dico_id', $_REQUEST) && is_numeric($_REQUEST['children_dico_id'])
             ) {
             $dicoItem = new flora\dico\DicoItem($GLOBALS['db']);
             $dicoItem->loadFromIdAndDico($_REQUEST['children_dico_id'],$_REQUEST['children_dico_item_id']);
             $dicoItem->setData($taxa->getData('id'), 'taxa_id');
             $dicoItem->replace();
         }
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa');
         exit(); 
      }
   }
   break; 
case 'delete' :
   $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $taxa->loadFromId($_REQUEST['id']);
      $taxa->delete();
   }
   exit;
   break;
case 'taxakindlist':
   $taxaKindColl = new \flora\taxa\TaxaKindColl($GLOBALS['db']);
   $taxaKindColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaKindColl->getItems() as $taxaKind) {
      $result[] = array(
          'label'=>$taxaKind->getData('name'),
          'value'=>$taxaKind->getData('id')
      );
   } 
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/taxa/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa/footer.phtml');  
break;
}