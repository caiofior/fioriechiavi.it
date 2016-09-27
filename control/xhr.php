<?php
if (!array_key_exists('task', $_REQUEST))
   $_REQUEST['task']=null;
if (!array_key_exists('action', $_REQUEST))
         $_REQUEST['action']=null;
switch ($_REQUEST['task']) {
   case 'user':
      switch ($_REQUEST['action']) {      
         case 'login':
            $result = (object) array('valid' => false);
            $auth = new Zend\Authentication\AuthenticationService();
            if (
                    array_key_exists('username', $_REQUEST) &&
                    array_key_exists('password', $_REQUEST)        
                    ) {
            $authAdapter = new \login\Auth($GLOBALS['db'],$_REQUEST['username'], $_REQUEST['password']);
            $authResult = $auth->authenticate($authAdapter);
            if ($authResult->getCode() == \Zend\Authentication\Result::SUCCESS) {
               $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'],$auth->getStorage()->read());
               $result = (object) $user->getData();
               unset($result->password,$result->confirm_code,$result->new_username,$result->change_datetime,$result->confirm_datetime,$result->profile_id);
               $result->valid = true;
               $result->token = $user->getProfile()->getData('token');
                    }
                    
            }
            if (array_key_exists('callback', $_REQUEST)) {
               echo $_REQUEST['callback'].'('.json_encode( $result ).')';
            } else {
               echo json_encode( $result );
            }
         break;
         case 'recover':
            $result = (object) array('valid' => false);
            if (
                    array_key_exists('usernameRecover', $_REQUEST) &&
                    $_REQUEST['usernameRecover']!='' && 
                    filter_var($_REQUEST['usernameRecover'], FILTER_VALIDATE_EMAIL)      
                    ) {
               $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'], $_REQUEST['usernameRecover']);
               if(is_object($user)) {
                  $result = (object) array('valid' => true);
                  $user->resetPassword();
              }
            }
            
            if (array_key_exists('callback', $_REQUEST)) {
                  echo $_REQUEST['callback'].'('.json_encode( $result ).')';
               } else {
                  echo json_encode( $result );
            }   
         break;
         case 'register':
            $result = (object) array('valid' => false);            
            if (
                    filter_var($_REQUEST['username'], FILTER_VALIDATE_EMAIL) &&
                    strlen($_REQUEST['password']) > 3
                  ) {
               $user = \login\user\LoginInstantiator::getLoginInstance($db, $_REQUEST['username']);
               if(is_object($user) && $user->getData('username') == '') {
                  $user = \login\user\LoginInstantiator::createLoginInstance($db, $_REQUEST['username'],$_REQUEST['password']);
                  $result = (object) $user->getData();
                  unset($result->password,$result->confirm_code,$result->new_username,$result->change_datetime,$result->confirm_datetime,$result->profile_id);
                  $result->valid = true;
                  $result->token = $user->getProfile()->getData('token');
               }
            }
            if (array_key_exists('callback', $_REQUEST)) {
               echo $_REQUEST['callback'].'('.json_encode( $result ).')';
            } else {
               echo json_encode( $result );
            }
         break;
      }
   break;
}
exit;