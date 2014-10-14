<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $dicoColl = new \flora\dico\DicoColl($GLOBALS['db']);
      $dicoColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$dicoColl->countAll();
      $result['iTotalDisplayRecords']=$dicoColl->count();
      $result['aaData']=array();
      $columns = $dicoColl->getColumns();
      foreach($dicoColl->getItems() as $key => $dico) {
         $row=array();
         foreach($columns as $column) {
            $data = $dico->getRawData($column);
            if ($column == 'actions') {
               $data = '<a class="actions modify" href="?task=dico&amp;action=edit&amp;id='.$dico->getData('id').'">Modifica</a><a class="actions delete" href="?task=dico&amp;action=delete&amp;id='.$dico->getData('id').'">Cancella</a>';
            } 
            $row[] = $data;     
         }
         $result['aaData'][]=$row;
      }
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
}
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case 'edit':
case 'deletetaxaassociation':
case 'update':
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   if ($_REQUEST['action'] == 'deletetaxaassociation') {
      $dicoItemColl = $dico->getDicoItemColl(); 
      $dicoItemColl = $dicoItemColl->filterByAttributeValue($_REQUEST['children_dico_item_id'], 'id');
      $dicoItemColl->getFirst()->removesTaxaAssociation();
   } else if ($_REQUEST['action'] == 'update') {
         $inputFile = sys_get_temp_dir()  . DIRECTORY_SEPARATOR . 'plupload'.DIRECTORY_SEPARATOR.$_REQUEST['filename'];
         $dico->dicoItemColl = $dico->importAndSave($_REQUEST['upload_format'],fopen($inputFile,'r'));  
   }
   $this->getTemplate()->setObjectData($dico);
   $this->getTemplate()->setBlock('middle','administrator/dico/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');  
   if (
           $_REQUEST['action'] == 'deletetaxaassociation' ||
           $_REQUEST['action'] == 'update'
      ) {
      header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=dico&action=edit&id='.$_REQUEST['id']);
      exit;
   }
   break; 
case 'deletetaxaitem':  
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $dicoItemColl = $dico->getDicoItemColl(); 
   $dicoItemColl = $dicoItemColl->filterByAttributeValue($_REQUEST['children_dico_item_id'], 'id');
   $dicoItemColl->getFirst()->delete();
   header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=dico&action=edit&id='.$_REQUEST['id']);
   exit;
break;
case 'createtaxaassociation':
      $dicoItem = new \flora\dico\DicoItem($GLOBALS['db']);
      $dicoItem->loadFromIdAndDico($_REQUEST['id'],$_REQUEST['id_dico']);
      $dicoItem->setData($_REQUEST['taxa_id'], 'taxa_id');
      $dicoItem->replace();
   exit;
   break;
case 'delete' :
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $dico->loadFromId($_REQUEST['id']);
      $dico->delete();
   }
   exit;
   break;
case 'jeditable' :
   if (
           array_key_exists('id_dico',$_REQUEST) &&
           is_numeric($_REQUEST['id_dico']) &&
           array_key_exists('id',$_REQUEST) &&
           strlen($_REQUEST['id']) > 1 &&
           is_numeric(substr($_REQUEST['id'],1))
       ) {
         $dicoItem = new \flora\dico\DicoItem($GLOBALS['db']);
         $dicoItem->loadFromIdAndDico($_REQUEST['id_dico'],substr($_REQUEST['id'],1));
         $dicoItem->setData($_REQUEST['value'], 'text');
         $dicoItem->replace();
         $dicoItem->loadFromIdAndDico($_REQUEST['id_dico'],substr($_REQUEST['id'],1));
         echo $dicoItem->getData('text');
         exit;
       }
   break;
case 'taxalist':
   $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
   $taxaColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaColl->getItems() as $taxa) {
      $result[] = array(
          'label'=>$taxa->getData('name'),
          'value'=>$taxa->getData('id')
      );
   } 
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
   break;
case 'download':
   header('Content-Description: File Transfer'); 
   header('Content-Type: text/csv; charset=utf-8'); 
   header('Content-Disposition: attachment; filename="'.$_REQUEST['id'].'.csv"'); 
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $dico->export($_REQUEST['download_format'], fopen('php://output', 'w+'));
   exit;
   break;
case 'upload':
   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
   header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
   header('Cache-Control: no-store, no-cache, must-revalidate');
   header('Cache-Control: post-check=0, pre-check=0', false);
   header('Pragma: no-cache');

   @set_time_limit(5 * 60);
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   $m->error = new stdClass();
   $m->id = 'id';

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
           fwrite($out, $buff);
   }

   @fclose($out);
   @fclose($in);

   // Check if file has been uploaded
   if (!$chunks || $chunk == $chunks - 1) {
           // Strip the temp .part suffix off 
           rename($filePath.'.part', $filePath);
   }
   
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   $m->result = '';
   $m->id = 'id';
   echo json_encode($m);
   exit;
   break;
case 'preview' :
   $dico = new \flora\dico\Dico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $inputFile = sys_get_temp_dir()  . DIRECTORY_SEPARATOR . 'plupload'.DIRECTORY_SEPARATOR.$_REQUEST['filename'];
   $dico->dicoItemColl = $dico->import($_REQUEST['upload_format'],fopen($inputFile,'r'));
   $this->getTemplate()->setObjectData($dico);
   $this->getTemplate()->setBlock('middle','administrator/dico/preview.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');  
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/dico/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dico/footer.phtml');  
break;
}