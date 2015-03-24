<?php
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
$enabled = strpos(exec('mysql --version'), 'mysql') === 0;
$enabled &= strpos(exec('mysqldump --version'), 'mysqldump') === 0;
$enabled &= $GLOBALS['config']->database->driver == 'Mysqli';
$enabled = (bool)$enabled;
switch ($_REQUEST['action']) {
case 'backup':
   $command = 'mysqldump ';
   if ($GLOBALS['config']->database->hostname != '') {
       $command .= ' -h '.$GLOBALS['config']->database->hostname;
   }
   if ($GLOBALS['config']->database->username != '') {
       $command .= ' -u '.$GLOBALS['config']->database->username;
   }
   if ($GLOBALS['config']->database->password != '') {
       $command .= ' -p'.$GLOBALS['config']->database->password;
   }
   $command .= ' '.$GLOBALS['config']->database->database.' taxa_kind region taxa taxa_region taxa_attribute taxa_attribute_value taxa_image --replace s--no-create-db --no-create-info ';
   $temporaryFileName= tempnam(sys_get_temp_dir(),'');
   $command .= ' >'.$temporaryFileName;
   exec($command);
   header('Content-Description: File Transfer');
   header('Content-Type: text/csv; charset=utf-8'); 
   header('Content-Disposition: attachment; filename="backup.sql"'); 
   echo file_get_contents($temporaryFileName);
   exit;
   break; 
default:
   if ($enabled === true) {
        $this->getTemplate()->setBlock('middle','administrator/backup/main.phtml');
   } else {
        $this->getTemplate()->setBlock('middle','administrator/backup/disabled.phtml');
   }
   $this->getTemplate()->setBlock('footer','administrator/backup/footer.phtml');  
break;
}