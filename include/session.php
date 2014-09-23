<?php
$manager = new \Zend\Session\SessionManager();
$manager->setName('abbrevia');
\Zend\Session\Container::setDefaultManager($manager);
$auth = new Zend\Authentication\AuthenticationService();
$db->session = $manager->getStorage();
if (
        key_exists('action', $_REQUEST) &&
        $_REQUEST['action']=='login' &&
        key_exists('xhrValidate', $_REQUEST)  &&
        key_exists('username', $_REQUEST) &&
        key_exists('password', $_REQUEST)
        ) {
   if (is_numeric(session_id())) session_destroy();
   $authAdapter = new login\Auth($db,$_REQUEST['username'], $_REQUEST['password']);
   $authResult = $auth->authenticate($authAdapter);
   if ($authResult->getCode() != \Zend\Authentication\Result::SUCCESS)
      $control->addValidationMessage('username_login','Credenziali errate');
   $db->session->plain_pwd= $_REQUEST['password'];
}
else if (
        key_exists('action', $_REQUEST) &&
        $_REQUEST['action']=='register' &&
        key_exists('username', $_REQUEST) &&
        key_exists('password', $_REQUEST)
        ) {
   $_REQUEST['task']='register';
   if(key_exists('xhrValidate', $_REQUEST)) {
      if (!filter_var($_REQUEST['username'], FILTER_VALIDATE_EMAIL)) {
         $control->addValidationMessage('username_register','Inserisci una mail valida');
      }
      if (sizeof($_REQUEST['password'])< 3) {
         $control->addValidationMessage('password_register','La password deve avere almeno tre caratteri');
      }
      if ($_REQUEST['password'] !== $_REQUEST['passwordr']) {
         $control->addValidationMessage('password_register','Le due password non coincidono');
      }
      if ($control->formIsValid()) {
         $user = \login\user\UserInstantiator::getUserInstance($db, $_REQUEST['username']);
         if($user->getData('username') != '') {
            $control->addValidationMessage('username_register','Utente già registrato');
         }
      }
   } else {
      $user = \login\user\UserInstantiator::createUserInstance($db, $_REQUEST['username'],$_REQUEST['password']);
   }
   $auth->getStorage()->clear();
   if (is_numeric(session_id())) session_destroy();
} else if (key_exists('confirmCode', $_REQUEST)){
   $_REQUEST['task']='confirm';
   try {
      $user = \login\user\UserInstantiator::confirmUserInstance($db, $_REQUEST['confirmCode']);
   } catch (\Exception $e) {
      switch ($e->getCode()) {
         case 1409011509 :
            $control->addValidationMessage('username_register','Utente non identificato');
            break;
         case 1409011510 :
            $control->addValidationMessage('username_register','Utente già autenticato');
            break;
         default:
            throw $e;
            break;
      }
   }
   $auth->getStorage()->clear();
   if (is_numeric(session_id())) session_destroy();
   
} else if (key_exists('logout', $_REQUEST)) {
   $auth->getStorage()->clear();
   if (is_numeric(session_id())) session_destroy();
}
$user = null;
try{
   $user = login\user\UserInstantiator::getUserInstance($db,$auth->getStorage()->read());
} catch (\Exception $e) {
   if ($e->getCode() != 1401231705)
      throw $e;
}
