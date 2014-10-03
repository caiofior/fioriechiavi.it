<?php
if (!array_key_exists('task', $_REQUEST))
   $_REQUEST['task']=null;
switch ($_REQUEST['task']) {
   case 'register' :
      $this->getTemplate()->setBlock('middle','user/register.phtml');      
   break;
   case 'recover' :
      if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
         $user = \login\user\UserInstantiator::getUserInstance($GLOBALS['db'], $_REQUEST['recover_login']);
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
         $user = \login\user\UserInstantiator::getUserInstance($GLOBALS['db'], $_REQUEST['recover_login']);
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
   default :
      if ($GLOBALS['user'] instanceof \login\user\User) {
         switch ($GLOBALS['user']->getData('role_id')) {
            case 1 :
               $this->getTemplate()->setBlock('middle','administrator/dashboard.phtml');      
               break;
            default :
               $this->getTemplate()->setBlock('middle','user/dashboard.phtml');            
               break;
         }
      } else {
         $this->getTemplate()->setBlock('middle','user/login.phtml');      
      }
   break;
}


