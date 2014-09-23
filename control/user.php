<?php
if (!array_key_exists('task', $_REQUEST))
   $_REQUEST['task']=null;
switch ($_REQUEST['task']) {
   case 'register' :
      $this->getTemplate()->setBlock('middle','user/register.phtml');      
   break;
   case 'confirm' :
      $this->getTemplate()->setBlock('middle','user/confirm.phtml');      
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


