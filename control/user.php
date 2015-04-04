<?php
$this->getTemplate()->setBlock('head','user/head.phtml');
if (!array_key_exists('task', $_REQUEST))
   $_REQUEST['task']=null;
if (array_key_exists('changeLoginConfirmCode', $_REQUEST)) {
   $_REQUEST['task']='changelogin';
}
switch ($_REQUEST['task']) {
   case 'register' :
      $this->getTemplate()->setBlock('middle','user/register.phtml');      
   break;
   case 'recover' :
      if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
         $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'], $_REQUEST['recover_login']);
         if(!is_object($user)) {
            $this->addValidationMessage('recover_login','La tua mail non si trova nei nostri archivi');
         }
         if (!array_key_exists('recover_login', $_REQUEST) ||$_REQUEST['recover_login']=='') {
             $this->addValidationMessage('recover_login','La mail è obbligatoria');
         }
         if (!filter_var($_REQUEST['recover_login'], FILTER_VALIDATE_EMAIL)) {
            $this->addValidationMessage('recover_login','Inserisci una mail valida');
         }
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'], $_REQUEST['recover_login']);
         $user->resetPassword();
         $this->getTemplate()->setBlock('middle','user/reset.phtml');    
      }
   break;
   case 'confirm' :
      $this->getTemplate()->setBlock('middle','user/confirm.phtml');      
   break;
   case 'changepassword':
      $this->getTemplate()->setBlock('middle','user/changepassword.phtml');
      if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
         $user = $GLOBALS['user'];
         if (!$user->checkPassword($_REQUEST['old_password'])) {
            $this->addValidationMessage('old_password','La vecchia password è errata');
         }     
         if (strlen($_REQUEST['new_password'])< 3) {
            $this->addValidationMessage('new_password','La password deve avere almeno tre caratteri');
         }
         if ($_REQUEST['new_password'] !== $_REQUEST['new_passwordr']) {
            $this->addValidationMessage('new_passwordr','Le due password non coincidono');
         }
         
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $user->setPassword($_REQUEST['new_password']);
         $this->getTemplate()->setBlock('middle','user/changepasswordconfirm.phtml');
      }
      break;
    case 'changelogin':
      $this->getTemplate()->setBlock('middle','user/changelogin.phtml');
      if (array_key_exists('changeLoginConfirmCode', $_REQUEST)) {
            $user = new \login\user\Login($GLOBALS['db']);
            $user->loadFromConfirmCode($_REQUEST['changeLoginConfirmCode']);
            if($user->getData('username') == '') {
                $this->addValidationMessage('username','Utente non valido');
            }
            if($user->getData('new_username') == '') {
               $this->addValidationMessage('username','Non c\'è un\'email da modificare');
            }
            $user->newLoginConfirmed();
         $this->getTemplate()->setBlock('middle','user/changeloginconfirmed.phtml');
      }
      if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
         
         if (!array_key_exists('new_login', $_REQUEST) ||$_REQUEST['new_login']=='') {
             $this->addValidationMessage('new_login','La mail è obbligatoria');
         }
         if (!filter_var($_REQUEST['new_login'], FILTER_VALIDATE_EMAIL)) {
            $this->addValidationMessage('new_login','Inserisci una mail valida');
         }
         if ($_REQUEST['new_login']==$GLOBALS['user']->getData('login')) {
            $this->addValidationMessage('new_login','La mail è uguale a quella già esistente');
         }
         if ($GLOBALS['user']->checkPassword($_REQUEST['old_password'])) {
            $this->addValidationMessage('old_password','Password errata');
         }                  
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $GLOBALS['user']->setNewLogin($_REQUEST['new_login']);
         $this->getTemplate()->setBlock('middle','user/changeloginwaiting.phtml');
      }
      break;
   case 'changeprofile':
      $this->getTemplate()->setBlock('middle','user/changeprofile.phtml');
      $profile = $GLOBALS['user']->getProfile();
      $this->getTemplate()->setObjectData($profile);
      if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
         if ($_REQUEST['phone'] != '' && preg_match('/[^0-9 +]/', $_REQUEST['phone'])) {
            $this->addValidationMessage('phone','Il teleefono può contenere numeri, spazio e "+"');
         }
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $profile->setData($_REQUEST);
         $profile->update();
         header('Location: '.$GLOBALS['db']->config->baseUrl.'user.php');
         exit;
      }
      break;
   case 'facebook' :
       require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'facebook.php';   
   break;
   default :
      if ($GLOBALS['profile'] instanceof \login\user\Profile) {
         switch ($GLOBALS['profile']->getData('role_id')) {
            case 1 :
               $this->getTemplate()->setBlock('middle','administrator/dashboard.phtml');      
               break;
            default :
               $this->getTemplate()->setBlock('middle','user/dashboard.phtml');            
               break;
         }
      } else {
         $this->getTemplate()->setBlock('middle','user/login.phtml');
         $this->getTemplate()->setBlock('footer','user/footer.phtml');  
      }
   break;
}


