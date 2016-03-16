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
               if ($profile->getRawData('active') ==1)
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
      if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
      if (!array_key_exists('role_id', $_REQUEST) ||$_REQUEST['role_id']=='') {
          $this->addValidationMessage('role_id','Il ruolo è obbligatorio');
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $_REQUEST['active']=(array_key_exists('active', $_REQUEST)?1:0); 
         $profile->setData($_REQUEST);
         $taxaList = array();
         if (array_key_exists('taxa_list', $_REQUEST) && is_array($_REQUEST['taxa_list'])) {
             $taxaList = $_REQUEST['taxa_list'];
         } 
         $profile->setEditableTaxa($taxaList);
         $profile->update();
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=user&action=view&id='.$profile->getData('id'));
         exit(); 
      }
   }
      $this->getTemplate()->setObjectData($profile);
      $this->getTemplate()->setBlock('middle','administrator/user/view.phtml');
      break;
   case 'isactive' :
      $profile = new \login\user\Profile($GLOBALS['db']);
      $profile->loadFromId($_REQUEST['id']);
      $profile->setData($_REQUEST['checked'], 'active');
      $profile->update();
      exit;
      break;
   case 'delete' :
      $profile = new \login\user\Profile($GLOBALS['db']);
      if (array_key_exists('id', $_REQUEST) && $_REQUEST['id'] != '') {
         $profile->loadFromId($_REQUEST['id']);
         $profile->delete();
      }
      exit;
   case 'taxalist' :
   $excludeTaxa = array();
   if (array_key_exists('taxa_list',$_REQUEST) && $_REQUEST['taxa_list'] != '') {
      parse_str ($_REQUEST['taxa_list'],$excludeTaxa);
      $excludeTaxa = $excludeTaxa['taxa_list'];
      
   }
   $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
   $taxaColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaColl->getItems() as $taxa) {
      if (in_array($taxa->getData('id'), $excludeTaxa)) {
         continue;
      }
      $result[] = array(
          'value'=>$taxa->getData('id'),
          'label'=>$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name')
      );
   } 
   header('Content-Type: application/json');
   header('Pragma: cache');
   header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
   header('Cache-Control: max-age=3600, must-revalidate, public ');
   echo json_encode($result);
   exit;
   break;
}