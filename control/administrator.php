<?php
if (!key_exists('task', $_REQUEST)) {
   $_REQUEST['task']=null;
}
switch ($_REQUEST['task']) {
   case 'user':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'user.php';    
      break;
   case 'dico':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'dico.php';       
      break;
   case 'taxa_category':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'taxa_category.php';       
      break;
   case 'taxa':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'taxa.php';       
      break;
   default:
      $this->getTemplate()->setBlock('middle','administrator/dashboard.phtml');
      break;
}


