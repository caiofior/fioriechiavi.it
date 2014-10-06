<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $contentColl = new \content\content\ContentColl($GLOBALS['db']);
      $contentColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$contentColl->count();
      $result['iTotalDisplayRecords']=$contentColl->countAll();
      $result['aaData']=array();
      $columns = $contentColl->getColumns();
      foreach($contentColl->getItems() as $key => $content) {
         $row=array();
         foreach($columns as $column) {
            $data = $content->getRawData($column);
            if ($column == 'content') {
               $data = strip_tags($data);
               if (strlen($data)>200)
                  $data = substr($data,0,200).'&#133;';
            } else if ($column == 'actions') {
               $data = '<a class="actions modify" title="Modifica" href="?task=content&amp;action=edit&amp;id='.$content->getData('id').'">Modifica</a><a class="actions delete" title="Cancella" href="?task=content&amp;action=delete&amp;id='.$content->getData('id').'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/content/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/content/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
      if (!array_key_exists('title', $_REQUEST) ||$_REQUEST['title']=='') {
          $this->addValidationMessage('title','Il titolo è obbligatorio');
      }
      $content = new \content\content\Content($GLOBALS['db']);
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $content = new \content\content\Content($GLOBALS['db']);
         $content->setData($_REQUEST);
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $content->update();
         } else {
            $content->insert();
         }
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=content');
         exit(); 
      }
   }
   break; 
case 'delete' :
   $content = new \content\content\Content($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {      
      $content->loadFromId($_REQUEST['id']);
      $content->delete();
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
         $content = new \content\content\Content($GLOBALS['db']);
         $content->loadFromId($_REQUEST['id']);
         $content->setData($_REQUEST['value'], 'name');
         $content->update();
         $content->loadFromId($_REQUEST['id']);
         echo $content->getData('name');
         exit;
       }
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/content/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/content/footer.phtml');  
break;
}