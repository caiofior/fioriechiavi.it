<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $userColl = new \login\user\UserColl($GLOBALS['db']);
      $userColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$userColl->count();
      $result['iTotalDisplayRecords']=$userColl->countAll();
      $result['aaData']=array();
      $columns = $userColl->getColumns();
      foreach($userColl->getItems() as $key => $user) {
         $row=array();
         foreach($columns as $column) {
            $data = $user->getRawData($column);
            if ($column == 'active') {
               $checked='';
               if ($data ==1)
                  $checked='checked="checked" ';
               $data = '<input '.$checked.'type="checkbox" name="active">';
            } else if ($column == 'actions') {
               $data = '';
               if ($user->getData('username') !== $GLOBALS['user']->getData('username')) {
                  $data = '<a class="actions view" title="Vedi dettagli" href="?task=user&amp;action=view&amp;id='.$user->getData('username').'">Vedi dettagli</a><a class="actions delete" title="Cancella" href="?task=user&amp;action=delete&amp;id='.$user->getData('username').'">Cancella</a>';
               }
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
$this->getTemplate()->setBlock('middle','administrator/user/list.phtml');
$this->getTemplate()->setBlock('footer','administrator/user/footer.phtml'); 
switch ($_REQUEST['action']) {
   case 'view':
      $user = new \login\user\User($GLOBALS['db']);
      $user->loadFromId($_REQUEST['id']);
      $this->getTemplate()->setObjectData($user->getProfile());
      $this->getTemplate()->setBlock('middle','administrator/user/view.phtml');
      break;
   case 'isactive' :
      $user = new \login\user\User($GLOBALS['db']);
      $user->loadFromId($_REQUEST['user_id']);
      $user->setData($_REQUEST['checked'], 'active');
      $user->update();
      exit;
      break;
   case 'delete' :
      $user = new \login\user\User($GLOBALS['db']);
      if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
         $user->loadFromId($_REQUEST['id']);
         $user->delete();
      }
      exit;
   break;
}