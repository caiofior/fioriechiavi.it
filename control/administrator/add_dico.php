<?php
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case 'edit':
case 'deletetaxaassociation':
case 'update':
   $dico = new \flora\dico\AddDico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $dicoItemColl = $dico->getDicoItemColl(); 
   if ($_REQUEST['action'] == 'deletetaxaassociation') {
      $dicoItemColl = $dicoItemColl->filterByAttributeValue($_REQUEST['children_dico_item_id'], 'id');
      $dicoItemColl->getFirst()->removesTaxaAssociation();
   } else if ($_REQUEST['action'] == 'update') {
         if (array_key_exists('filename',$_REQUEST) && $_REQUEST['filename'] != '') {
            $inputFile = sys_get_temp_dir()  . DIRECTORY_SEPARATOR . 'plupload'.DIRECTORY_SEPARATOR.$_REQUEST['filename'];
            $dicoItemColl = $dicoItemColl->importAndSave($_REQUEST['upload_format'],fopen($inputFile,'r'));  
         } else if (array_key_exists('dicotext',$_REQUEST)) {
            $resouce = fopen('php://memory', 'rw+');
            fwrite($resouce, $_REQUEST['dicotext']);
            rewind($resouce);
            $dicoItemColl = $dicoItemColl->importAndSave($_REQUEST['upload_format'],$resouce);  
         }
   } else if ($_REQUEST['action'] == 'edit' && array_key_exists('submit', $_REQUEST)) {
       if (!array_key_exists('is_list', $_REQUEST)) {
            $_REQUEST['is_list']=0;
       }
       
       if ($dico->getData('is_list') == 1) {
         
            $lastId = '';
            $dicoItemColl->emptyDicoItems();
            if (array_key_exists('text', $_REQUEST) && is_array($_REQUEST['text'])) {
               foreach($_REQUEST['text'] as $key=>$text) {
                     if (
                             substr($lastId,-1) == '1' ||
                             $lastId==''
                     ) {
                        $id = $lastId.'0';
                     } else {
                        $id = substr($lastId,0,-1).'1';
                     }

                     $dicoItem = $dicoItemColl->addItem();
                     
                     $dicoItem->setData(array(
                         'id'=>$id,
                         'text'=>$text,
                         'taxa_id'=>$_REQUEST['taxa_id'][$key]
                     ));
                     
                     $dicoItem->replace();
                     $lastId=$id;
               }

            } 

            if (array_key_exists('addText', $_REQUEST) && $_REQUEST['addText'] != '') {
               if (!array_key_exists('addPhotoId', $_REQUEST)) {
                  $_REQUEST['addPhotoId']='';
               }
               if (substr($lastId,-1) == '1') {
                  $id = $lastId.'0';
               } else {
                  $id = substr($lastId,0,-1).'1';
               }
               $dicoItem = $dicoItemColl->addItem();
               $dicoItem->setData(array(
                   'id'=>$id,
                   'text'=>$_REQUEST['addText'],
                   'photo_name'=>$_REQUEST['addPhotoId']
               ));
               $dicoItem->replace();
            }
         
      } else {
         $dico->setData($_REQUEST);
         $dico->update();
        }
   }
   $this->getTemplate()->setObjectData($dico);
   $this->getTemplate()->setBlock('middle','administrator/add_dico/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/add_dico/footer.phtml');  
   if (
           $_REQUEST['action'] == 'deletetaxaassociation' ||
           $_REQUEST['action'] == 'update'
      ) {
      header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=add_dico&action=edit&id='.$_REQUEST['id']);
      exit;
   }
   break; 
case 'deletetaxaitem':  
   $dico = new \flora\dico\AddDico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $dicoItemColl = $dico->getDicoItemColl(); 
   $dicoItemColl = $dicoItemColl->filterByAttributeValue($_REQUEST['children_dico_item_id'], 'id');
   $dicoItemColl->getFirst()->delete();
   
   header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=add_dico&action=edit&id='.$_REQUEST['id']);
   exit;
break;
case 'createtaxaassociation':
    $dicoItem = new \flora\dico\AddDicoItem($GLOBALS['db']);
    $dicoItem->loadFromIdAndDico($_REQUEST['id'],$_REQUEST['id_dico']);
    $dicoItem->setData($_REQUEST['taxa_id'], 'taxa_id');
    $dicoItem->replace();
   exit;
   break;
case 'delete' :
   $dico = new \flora\dico\AddDico($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $dico->loadFromId($_REQUEST['id']);
      $dico->getDicoItemColl()->emptyDicoItems();
   }
   header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=add_dico&action=edit&id='.$_REQUEST['id']);
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
         $dicoItem = new \flora\dico\AddDicoItem($GLOBALS['db']);
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
   $_REQUEST['doNotCreate']=1;
   $_REQUEST['iDisplayStart']=0;
   $_REQUEST['iDisplayLength']=10;
   $taxaColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaColl->getItems() as $taxa) {
      $result[] = array(
          'label'=>$taxa->getData('name'),
          'value'=>$taxa->getData('id')
      );
   } 
   header('Content-Type: application/json');
   header('Pragma: cache');
   header('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600).' GMT');
   header('Cache-Control: max-age=3600, must-revalidate, public ');
   echo json_encode($result);
   exit;
   break;
case 'download':
   header('Content-Description: File Transfer'); 
   header('Content-Type: text/csv; charset=utf-8'); 
   header('Content-Disposition: attachment; filename="'.$_REQUEST['id'].'.csv"'); 
   $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
   $taxa->loadFromId($_REQUEST['id']);
   $taxa->getDicoItemColl()->export($_REQUEST['download_format'], fopen('php://output', 'w+'));
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
   
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   $m->result = '';
   $m->id = 'id';
   echo json_encode($m);
   exit;
   break;
case 'preview' :
   $dico = new \flora\dico\AddDico($GLOBALS['db']);
   $dico->loadFromId($_REQUEST['id']);
   $dicoItemColl = $dico->getDicoItemColl();
   if (array_key_exists('filename',$_REQUEST) && $_REQUEST['filename'] != '') {
      $inputFile = sys_get_temp_dir()  . DIRECTORY_SEPARATOR . 'plupload'.DIRECTORY_SEPARATOR.$_REQUEST['filename'];
      $resouce = fopen($inputFile,'r');
   } else if (array_key_exists('dicotext',$_REQUEST)) {
      $resouce = fopen('php://memory', 'rw+');
      fwrite($resouce, $_REQUEST['dicotext']);
      rewind($resouce);
   }
   $this->getTemplate()->setObjectData($dico);
   $this->getTemplate()->setBlock('middle','administrator/add_dico/preview.phtml');
   $this->getTemplate()->setBlock('footer','administrator/add_dico/footer.phtml');  
   try{
   if (!array_key_exists('upload_format', $_REQUEST)) {
       throw new \Exception('Missing input parameter',1511041211);
   }
   $dico->dicoItemColl = $dicoItemColl->import($_REQUEST['upload_format'],$resouce);
   } catch (\Exception $e) {
      $this->getTemplate()->setBlock('middle','administrator/add_dico/edit.phtml');   
   }
   
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/taxa/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa/footer.phtml');  
break;
}