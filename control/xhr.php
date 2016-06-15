<?php
if (!array_key_exists('task', $_REQUEST))
   $_REQUEST['task']=null;
if (!array_key_exists('action', $_REQUEST))
         $_REQUEST['action']=null;
switch ($_REQUEST['task']) {
   case 'user':
      switch ($_REQUEST['action']) {      
         case 'login':
            $auth = new Zend\Authentication\AuthenticationService();
            $authAdapter = new \login\Auth($GLOBALS['db'],$_REQUEST['username'], $_REQUEST['password']);
            $authResult = $auth->authenticate($authAdapter);
            $result = (object) array('valid' => false);
            if ($authResult->getCode() == \Zend\Authentication\Result::SUCCESS) {
               $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'],$auth->getStorage()->read());
               $result = (object) $user->getData();
               unset($result->password,$result->confirm_code,$result->new_username,$result->change_datetime,$result->confirm_datetime,$result->profile_id);
               $result->valid = true;
               $result->token = $user->getProfile()->getData('token');
            }
            echo $_REQUEST['callback'].'('.json_encode( $result ).')';
         break;
      }
   break;
}
exit;