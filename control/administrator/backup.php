<?php
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
$enabled = strpos(exec('mysql --version'), 'mysql') === 0;
$enabled &= strpos(exec('mysqldump --version'), 'mysqldump') === 0;
$enabled &= $GLOBALS['config']->database->driver == 'Mysqli';
$enabled = (bool)$enabled;
switch ($_REQUEST['action']) {
case 'backuptaxa':
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
   $command .= ' '.$GLOBALS['config']->database->database.' taxa_kind region taxa taxa_region taxa_attribute taxa_attribute_value taxa_image --replace --no-create-db --no-create-info ';
   $temporaryFileName= tempnam(sys_get_temp_dir(),'');
   $command .= ' -r '.$temporaryFileName;
   exec($command);
   header('Content-Description: File Transfer');
   header('Content-Type: text/plain; charset='.mysqli_character_set_name($GLOBALS['db']->getDriver()->getConnection()->getResource())); 
   header('Content-Disposition: attachment; filename="backup_taxa_'.date('Y-m-d').'.sql"'); 
   echo file_get_contents($temporaryFileName);
   exit;
   break; 
case 'backuputenti':
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
   $command .= ' '.$GLOBALS['config']->database->database.' profile_kind profile login facebook facebook_graph contact contact_parent --replace --no-create-db --no-create-info ';
   $temporaryFileName= tempnam(sys_get_temp_dir(),'');
   $command .= ' -r '.$temporaryFileName;
   exec($command);
   header('Content-Description: File Transfer');
   header('Content-Type: text/plain; charset='.mysqli_character_set_name($GLOBALS['db']->getDriver()->getConnection()->getResource()));
   header('Content-Disposition: attachment; filename="backup_utenti_'.date('Y-m-d').'.sql"'); 
   echo file_get_contents($temporaryFileName);
   exit;
   break; 
case 'restore' :
   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
   header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
   header('Cache-Control: no-store, no-cache, must-revalidate');
   header('Cache-Control: post-check=0, pre-check=0', false);
   header('Pragma: no-cache');

   @set_time_limit(5 * 60);
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   $m->error = new stdClass();

   // Uncomment this one to fake upload time
   // usleep(5000);

   $targetDir = sys_get_temp_dir()  . DIRECTORY_SEPARATOR . 'plupload';
   $cleanupTargetDir = true; // Remove old files
   $maxFileAge = 5 * 3600; // Temp file age in seconds
   if (!file_exists($targetDir)) {
           @mkdir($targetDir);
   }
   if (isset($_REQUEST['name'])) {
           $fileName = $_REQUEST['name'];
   } elseif (!empty($_FILES)) {
           $fileName = $_FILES['file']['name'];
   } else if ($in = fopen('php://input', 'rb')) {
      $nextIsName=false;
      while ($buff = fgets($in, 4096)) {
         if ($nextIsName) {
            $buff = trim($buff);
            if ($buff != '') {
               $fileName = $buff;
               break;
            }
         } else if (preg_match('/name="name"/',$buff)) {
            $nextIsName=true;
         }
      }
   } else {
           $fileName = uniqid('file_');
   }

   $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

   $chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
   $chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;


   if ($cleanupTargetDir) {
           if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                   $m->error->code = 100;
                   $m->error->message = 'Directory temporanea non disponibile '.$targetDir;
                   echo json_encode($m);
                   exit;
           }

           while (($file = readdir($dir)) !== false) {
                   $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                   if ($tmpfilePath == $filePath.'.part') {
                           continue;
                   }
                   if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                           @unlink($tmpfilePath);
                   }
           }
           closedir($dir);
   }	


   if (!$out = @fopen($filePath.'.part', $chunks ? 'ab' : 'wb')) {
                   $m->error->code = 102;
                   $m->error->message = 'Errore nell\'apertura del flusso in input';
                   echo json_encode($m);
                   exit;
   }

   if (!empty($_FILES)) {
           if ($_FILES['file']['error'] || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                   $m->error->code = 103;
                   $m->error->message = 'Errore nello spostamento del file caricato';
                   echo json_encode($m);
                   exit;
           }

           // Read binary input stream and append it to temp file
           if (!$in = @fopen($_FILES['file']['tmp_name'], 'rb')) {
                   $m->error->code = 101;
                   $m->error->message = 'Errore nello spostamento del file caricato';
                   echo json_encode($m);
                   exit;
           }
   } else {	
           if (!$in = @fopen('php://input', 'rb')) {
                   $m->error->code = 101;
                   $m->error->message = 'Errore nello spostamento del file caricato';
                   echo json_encode($m);
                   exit;
           }
   }

   while ($buff = fread($in, 4096)) {
            $buff = preg_replace('/-{10,}.*\r\n\r\n/s','', $buff);
            $buff = preg_replace('/-{10,}.*-{2}/s','', $buff);
            fwrite($out, $buff);
   }

   @fclose($out);
   @fclose($in);

   // Check if file has been uploaded
   if (!$chunks || $chunk == $chunks - 1) {
           // Strip the temp .part suffix off 
           rename($filePath.'.part', $filePath);
   }
   
   $command = 'mysql ';
   if ($GLOBALS['config']->database->hostname != '') {
       $command .= ' -h '.$GLOBALS['config']->database->hostname;
   }
   if ($GLOBALS['config']->database->username != '') {
       $command .= ' -u '.$GLOBALS['config']->database->username;
   }
   if ($GLOBALS['config']->database->password != '') {
       $command .= ' -p'.$GLOBALS['config']->database->password;
   }
   $command .= ' '.$GLOBALS['config']->database->database.' ';
   $command .= ' < '.$filePath;
   exec($command);
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   echo json_encode($m);
   exit;
    break;
case 'resettaxa':
    $command = 'mysql ';
   if ($GLOBALS['config']->database->hostname != '') {
       $command .= ' -h '.$GLOBALS['config']->database->hostname;
   }
   if ($GLOBALS['config']->database->username != '') {
       $command .= ' -u '.$GLOBALS['config']->database->username;
   }
   if ($GLOBALS['config']->database->password != '') {
       $command .= ' -p'.$GLOBALS['config']->database->password;
   }
   $command .= ' '.$GLOBALS['config']->database->database.' ';
   $command .= ' -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE taxa_kind; TRUNCATE TABLE region; TRUNCATE TABLE taxa; TRUNCATE TABLE taxa_region; TRUNCATE TABLE taxa_attribute; TRUNCATE TABLE taxa_attribute_value; TRUNCATE TABLE taxa_image;SET FOREIGN_KEY_CHECKS=1"';
   exec($command);
   $this->getTemplate()->setBlock('middle','administrator/backup/main.phtml');
   $this->getTemplate()->setBlock('footer','administrator/backup/footer.phtml');  
   break; 
case 'resetutenti':
    $command = 'mysql ';
   if ($GLOBALS['config']->database->hostname != '') {
       $command .= ' -h '.$GLOBALS['config']->database->hostname;
   }
   if ($GLOBALS['config']->database->username != '') {
       $command .= ' -u '.$GLOBALS['config']->database->username;
   }
   if ($GLOBALS['config']->database->password != '') {
       $command .= ' -p'.$GLOBALS['config']->database->password;
   }
   $command .= ' '.$GLOBALS['config']->database->database.' ';
   $command .= ' -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE user; TRUNCATE TABLE profile; TRUNCATE TABLE contact; TRUNCATE TABLE contact_parent;SET FOREIGN_KEY_CHECKS=1"';
   exec($command);
   $this->getTemplate()->setBlock('middle','administrator/backup/main.phtml');
   $this->getTemplate()->setBlock('footer','administrator/backup/footer.phtml');  
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