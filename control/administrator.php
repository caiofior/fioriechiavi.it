<?php
require __DIR__.'/../lib/log/Autoload.php';
log\Autoload::getInstance();
if (!array_key_exists('task', $_REQUEST)) {
   $_REQUEST['task']=null;
}
if (
      !isset($GLOBALS['profile']) ||
      !is_object($GLOBALS['profile']) ||
      !$GLOBALS['profile'] instanceof \login\user\Profile
      ) {
   header('Location: '.$GLOBALS['db']->config->baseUrl.'user.php');
   exit;
}
switch ($_REQUEST['task']) {
   case 'user':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'user.php';    
      break;
   case 'dico':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'dico.php';       
      break;
   case 'add_dico':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'add_dico.php';       
      break;
   case 'taxa_category':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'taxa_category.php';       
      break;
   case 'taxa':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'taxa.php';       
      break;
   case 'region':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'region.php';       
      break;
   case 'attribute':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'attribute.php';       
      break;
   case 'content':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'content.php';       
      break;
   case 'contact':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'contact.php';       
      break;
   case 'log':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'log.php';       
      break;
   case 'backup':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'backup.php';       
      break;
   case 'observation':
      require __DIR__.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'observation.php';
      break;
   default:
      $this->getTemplate()->setBlock('middle','administrator/dashboard.phtml');
      break;
}


