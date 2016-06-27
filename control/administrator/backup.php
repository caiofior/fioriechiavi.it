<?php
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
$enabled = true;
switch ($_REQUEST['action']) {
case 'backuptaxa':
   require $GLOBALS['db']->baseDir.'/lib/shuttle-export/dumper.php';

   $world_dumper = \Shuttle_Dumper::create(array(
    'host' => $GLOBALS['config']->database->hostname,
    'username' => $GLOBALS['config']->database->username,
    'password' => $GLOBALS['config']->database->password,
    'db_name' => $GLOBALS['config']->database->database,
    'include_tables' => array('taxa_kind', 'region', 'taxa', 'taxa_region', 'taxa_attribute', 'taxa_attribute_value', 'taxa_image', 'dico_item', 'add_dico', 'add_dico_item'),
    'charset'=>$GLOBALS['config']->database->charset
   ));
   $temporaryFileName= tempnam(sys_get_temp_dir(),'');
   $world_dumper->dump($temporaryFileName);
   header('Content-Description: File Transfer');
   header('Content-Type: text/plain; charset='.mysqli_character_set_name($GLOBALS['db']->getDriver()->getConnection()->getResource()));
   header('Content-Disposition: attachment; filename="backup_taxa_'.date('Y-m-d').'.sql"'); 
   echo file_get_contents($temporaryFileName);
   
   exit;
   break; 
case 'backuputenti':
   require $GLOBALS['db']->baseDir.'/lib/shuttle-export/dumper.php';

   $world_dumper = \Shuttle_Dumper::create(array(
    'host' => $GLOBALS['config']->database->hostname,
    'username' => $GLOBALS['config']->database->username,
    'password' => $GLOBALS['config']->database->password,
    'db_name' => $GLOBALS['config']->database->database,
    'include_tables' => array('profile_role', 'profile', 'login', 'facebook', 'facebook_graph', 'contact'),
    'charset'=>$GLOBALS['config']->database->charset
   ));
   $temporaryFileName= tempnam(sys_get_temp_dir(),'');
   $world_dumper->dump($temporaryFileName);
   
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
   $fileName = uniqid('file_');
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
   
   require $GLOBALS['db']->baseDir.'/lib/noLimitDumpRestore/noLimitDumpRestore.php';
   noLimitDumpRestore($GLOBALS['db'],$filePath);
   
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   echo json_encode($m);
   exit;
    break;
case 'resettaxa':
   
   $GLOBALS['db']->query('TRUNCATE TABLE taxa_kind'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE region'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE taxa'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE taxa_region'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

   $GLOBALS['db']->query('TRUNCATE TABLE taxa_attribute'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE taxa_attribute_value'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE taxa_image'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE dico_item'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

   $GLOBALS['db']->query('TRUNCATE TABLE add_dico'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);   
   
   $GLOBALS['db']->query('TRUNCATE TABLE add_dico_item'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);  
   
   header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=backup');  
   exit();  
   break; 
case 'resetutenti':
   $GLOBALS['db']->query('TRUNCATE TABLE login'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE facebook'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE facebook_graph'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE profile'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE profile_taxa'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE contact'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   $GLOBALS['db']->query('TRUNCATE TABLE contact_parent'
             , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   
   header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=backup');  
   exit();
   break;
case 'reindex':
   $logDir = $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'log';
   if (!is_dir($logDir)) {
       mkdir($logDir);
   }
   pclose(popen('php ' . $GLOBALS['db']->baseDir . 'shell/indexing_taxa_search.php > '.$logDir.'/reindex.txt 2>&1 &', 'r'));
   header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=backup');  
   exit();
   break;
case 'reindex_ajax':
    require __DIR__ .'/../../shell/sitemap.php';
    require __DIR__ .'/../../shell/indexing_taxa_search.php';
    exit();
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