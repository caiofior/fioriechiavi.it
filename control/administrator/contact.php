<?php
require __DIR__.'/../../lib/contact/Autoload.php';
contact\Autoload::getInstance();
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $contactColl = new \contact\contact\ContactColl($GLOBALS['db']);
      $contactColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$contactColl->countAll($request);
      $result['iTotalDisplayRecords']=$contactColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $contactColl->getColumns();
      foreach($contactColl->getItems() as $key => $contact) {
         $row=array();
         foreach($columns as $column) {
            $data = $contact->getRawData($column);
            if ($column == 'content') {
               $data = strip_tags($data);
               if (strlen($data)>200)
                  $data = substr($data,0,200).'&#133;';
            } else if ($column == 'actions') {
               $data = '<a class="actions modify" title="Dettaglio" href="?task=contact&amp;action=edit&amp;id='.$contact->getData('id').'">Modifica</a><a class="actions delete" title="Cancella" href="?task=contact&amp;action=delete&amp;id='.$contact->getData('id').'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/contact/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/contact/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
      if (!array_key_exists('id', $_REQUEST) ||$_REQUEST['id']=='') {
            $this->addValidationMessage('message','Il messaggio a cui rispondere è obbligatorio');
      }
      if (!array_key_exists('message', $_REQUEST) ||$_REQUEST['message']=='') {
            $this->addValidationMessage('message','Il messaggio è obbligatorio');
      }
      $fromContact = new \contact\contact\Contact($GLOBALS['db']);
      $fromContact->loadFromId($_REQUEST['id']);
      $contact = new \contact\contact\Contact($GLOBALS['db']);
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {

         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=contact');
         exit(); 
      }
   }
   break; 
case 'delete' :
   $contact = new \contact\contact\Contact($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {      
      $contact->loadFromId($_REQUEST['id']);
      $contact->delete();
   }
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/contact/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/contact/footer.phtml');  
break;
}