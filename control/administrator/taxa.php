<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $taxaColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$taxaColl->countAll();
      $result['iTotalDisplayRecords']=$taxaColl->count();
      $result['aaData']=array();
      $columns = $taxaColl->getColumns();
      foreach($taxaColl->getItems() as $key => $taxa) {
         $row=array();
         foreach($columns as $column) {
            $data = $taxa->getRawData($column);
            if ($column == 'taxa_kind_id') {
               $data=$taxa->getRawData('taxa_kind_initials');
            } else if ($column == 'actions') {
               $data = '<a class="actions modify" title="Modifica" href="?task=taxa&amp;action=edit&amp;id='.$taxa->getData('id').'">Modifica</a><a class="actions delete" title="Cancella" href="?task=taxa&amp;action=delete&amp;id='.$taxa->getData('id').'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/taxa/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST) ||
            array_key_exists('submit_create_key', $_REQUEST)
      ) {
      if (!array_key_exists('taxa_kind_id', $_REQUEST) ||$_REQUEST['taxa_kind_id']=='') {
          $this->addValidationMessage('taxa_kind_id','Il tipo di tassonomia è obbligatorio');
      }
      if (!array_key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
          $this->addValidationMessage('name','Il nome è obbligatorio');
      }
      if (
              (
              array_key_exists('submit', $_REQUEST) ||
              array_key_exists('submit_create_key', $_REQUEST) 
              ) && $this->formIsValid()) {
         if (array_key_exists('submit_create_key', $_REQUEST)) {
            $dico = new \flora\dico\Dico($GLOBALS['db']);
            $dico->insert();
            $_REQUEST['dico_id']=$dico->getData('id');
         }
         
         $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxa->loadFromId($_REQUEST['id']);
         }
         $taxa->setData($_REQUEST);
         $taxa->setRegions($_REQUEST['regions']);
         
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxa->update();
         } else {
            $taxa->insert();
         }
         if (
                 array_key_exists('children_dico_item_id', $_REQUEST) && is_numeric($_REQUEST['children_dico_item_id']) &&
                 array_key_exists('children_dico_id', $_REQUEST) && is_numeric($_REQUEST['children_dico_id'])
             ) {
             $dicoItem = new flora\dico\DicoItem($GLOBALS['db']);
             $dicoItem->loadFromIdAndDico($_REQUEST['children_dico_id'],$_REQUEST['children_dico_item_id']);
             $dicoItem->setData($taxa->getData('id'), 'taxa_id');
             $dicoItem->replace();
         }
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa');
         exit(); 
      }
   } else if (
           array_key_exists('id', $_REQUEST) && 
           $_REQUEST['id'] != '' &&
           array_key_exists('attribute_name', $_REQUEST) && 
           $_REQUEST['attribute_name'] != '' &&
           array_key_exists('attribute_value', $_REQUEST) && 
           $_REQUEST['attribute_value'] != ''
           ) {
            $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
            $taxa->loadFromId($_REQUEST['id']);
            $taxa->addAttribute($_REQUEST['attribute_name'],$_REQUEST['attribute_value']);
            exit();
           }
   break; 
case 'delete_attribute':
   if (
           array_key_exists('id', $_REQUEST) && 
           $_REQUEST['id'] != '' &&
           array_key_exists('attribute_id', $_REQUEST) && 
           $_REQUEST['attribute_id'] != ''
           ) {
            $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
            $taxa->loadFromId($_REQUEST['id']);
            $taxa->deleteAttributeById($_REQUEST['attribute_id']);
            exit();
           }
   break;
case 'delete' :
   $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $taxa->loadFromId($_REQUEST['id']);
      $taxa->delete();
   }
   exit;
   break;
case 'taxaattributelist' :
   $taxaAttributeColl = new \flora\taxa\TaxaAttributeColl($GLOBALS['db']);
   $taxaAttributeColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaAttributeColl->getItems() as $taxaAttribute) {
      $result[] = array(
          'label'=>$taxaAttribute->getData('name')
      );
   } 
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
   break;
case 'reloadattribute' :
   $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $taxa->loadFromId($_REQUEST['id']);
   }
   require __DIR__.'/../../view/administrator/taxa/attributeBlock.phtml';
   exit;
   break;
case 'jeditable':
   
   if (
           array_key_exists('taxa_id', $_REQUEST) && 
           is_numeric($_REQUEST['taxa_id']) &&
           array_key_exists('id', $_REQUEST) && 
           $_REQUEST['id'] != '' &&
           array_key_exists('value', $_REQUEST) && 
           $_REQUEST['value'] != ''
      ) {
      $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
      $taxa->loadFromId($_REQUEST['taxa_id']);
      $taxa->addAttributeById(substr($_REQUEST['id'],4),$_REQUEST['value']);
      echo $taxa->getAttributeById(substr($_REQUEST['id'],4));
   }
   
   exit;
   break;
case 'taxakindlist':
   $taxaKindColl = new \flora\taxa\TaxaKindColl($GLOBALS['db']);
   $taxaKindColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaKindColl->getItems() as $taxaKind) {
      $result[] = array(
          'label'=>$taxaKind->getData('name'),
          'value'=>$taxaKind->getData('id')
      );
   } 
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
   break;
case 'imageupload':
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
   $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
   $taxa->loadFromId($_REQUEST['taxa_id']);
   $taxaImageColl = $taxa->getTaxaImgeColl();
   $taxaImage = $taxaImageColl->addItem();
   $taxaImage->moveInsert($filePath);
   
   $m = new stdClass();
   $m->jsonrpc = 2.0;
   $m->result = '';
   $m->id = 'id';
   echo json_encode($m);
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/taxa/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa/footer.phtml');  
break;
}