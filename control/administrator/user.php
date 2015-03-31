<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $profileColl = new \login\user\ProfileColl($GLOBALS['db']);
      $profileColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$profileColl->countAll($request);
      $result['iTotalDisplayRecords']=$profileColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $profileColl->getColumns();
      foreach($profileColl->getItems() as $key => $profile) {
         $row=array();
         foreach($columns as $column) {
            $user = $profile->getUserColl()->getFirst(); 
            $data = $profile->getRawData($column);
            if ($column == 'username') {
                $data = $user->getRawData('username');
                if ($data == '') {
                    $data = $profile->getRawData('email');
                }
            }
            if ($column == 'creation_datetime' || $column == 'last_login_datetime') {
                $data = $user->getRawData($column);
            }
            if ($column == 'active') {
               $checked='';
               if ($user->getRawData('active') ==1)
                  $checked='checked="checked" ';
               $data = '<input '.$checked.'type="checkbox" name="active">';
            } else if ($column == 'actions') {
               $data = '';
               if ($user->getData('username') !== $GLOBALS['user']->getData('username')) {
                  $data = '<a class="actions view" title="Vedi dettagli" href="?task=user&amp;action=view&amp;id='.$profile->getData('id').'">Vedi dettagli</a><a class="actions delete" title="Cancella" href="?task=user&amp;action=delete&amp;id='.$profile->getData('id').'">Cancella</a>';
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
      $profile = new \login\user\Profile($GLOBALS['db']);
      $profile->loadFromId($_REQUEST['id']);
      $this->getTemplate()->setObjectData($profile);
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
      $profile = new \login\user\Profile($GLOBALS['db']);
      if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {
         $profile->loadFromId($_REQUEST['id']);
         $profile->delete();
      }
      exit;
   break;
}