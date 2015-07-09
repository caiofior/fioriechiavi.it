<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $taxaColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$taxaColl->countAll($request);
      $result['iTotalDisplayRecords']=$taxaColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $taxaColl->getColumns();
      foreach($taxaColl->getItems() as $key => $taxa) {
         $row=array();
         foreach($columns as $column) {
            $data = $taxa->getRawData($column);
            if ($column == 'description') {
               $data = strip_tags($data);
               if (strlen($data)>200)
                  $data = substr($data,0,200).'&#133;';
            } else if ($column == 'taxa_kind_id') {
               $data=$taxa->getRawData('taxa_kind_initials');
            } else if ($column == 'actions') {
               if($taxa->getData('id') > 1) {
               $data = '
                   <a class="actions modify" title="Modifica" href="?task=taxa&amp;action=edit&amp;id='.$taxa->getData('id').'">Modifica</a>
                   <a class="actions delete" title="Cancella" href="?task=taxa&amp;action=delete&amp;id='.$taxa->getData('id').'">Cancella</a>
                   ';
               } else {
                   $data = '
                   <a class="actions modify" title="Modifica" href="?task=dico&amp;action=edit&amp;id='.$taxa->getData('id').'">Chiave dicotomica</a>
                   ';
               }
               
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
            array_key_exists('submit_back', $_REQUEST) ||
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
              array_key_exists('submit_back', $_REQUEST) ||
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

         $action='Crea';
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $action='Modifica';
            $taxa->update();
         } else {
            $taxa->insert();
         }
         if (key_exists('regions', $_REQUEST)) {
            $taxa->setRegions($_REQUEST['regions']);
         }
         
         $taxa->deleteAllTaxaAttributes();
         
         if (
                 array_key_exists('attribute_name_list', $_REQUEST) &&
                 is_array($_REQUEST['attribute_name_list']) &&
                 array_key_exists('attribute_value_list', $_REQUEST) &&
                 is_array($_REQUEST['attribute_value_list'])
                 ) {
            foreach ($_REQUEST['attribute_value_list'] as $attributeKey => $attributeValue) {
               if (
                     $attributeValue == '' ||
                     !array_key_exists($attributeKey, $_REQUEST['attribute_name_list']) ||
                     $_REQUEST['attribute_name_list'][$attributeKey] == ''
                  ) continue;
               $attributeName = $_REQUEST['attribute_name_list'][$attributeKey];
               $taxa->addTaxaAttribute($attributeName, $attributeValue);
               
            }   
         }
         $taxaImageColl = $taxa->getTaxaImageColl();
         if (
               array_key_exists('image_id_list', $_REQUEST) &&
               is_array($_REQUEST['image_id_list'])
            ) {
               
            $idc = $taxaImageColl->getFieldsAsArray('id');
            $taxaImage = new \flora\taxa\TaxaImage($GLOBALS['db']);
            foreach(array_diff($idc,$_REQUEST['image_id_list']) as $id) {
               $taxaImage->loadFromId($id);
               $taxaImage->delete();
            }

         }
        $taxaImageColl = $taxa->getTaxaImageColl();
         
         
         if (
            array_key_exists('image_name_list', $_REQUEST) &&
            is_array($_REQUEST['image_name_list'])
            ) {
                foreach ($_REQUEST['image_name_list'] as $imageName) {
                   if (
                           $imageName == '' ||
                           !is_file($GLOBALS['db']->baseDir  . DIRECTORY_SEPARATOR . 'tmp'.DIRECTORY_SEPARATOR.$imageName)
                           ) continue;
                   $taxaImage = $taxaImageColl->addItem();
                   $taxaImage->moveInsert($GLOBALS['db']->baseDir  . DIRECTORY_SEPARATOR . 'tmp'.DIRECTORY_SEPARATOR.$imageName);
                }
            }
            
            if (
                    isset($_FILES) &&
                    is_array($_FILES) &&
                    is_array($_FILES['traditional_image']) && 
                    $_FILES['traditional_image']['error'] ==0 &&
                    $_FILES['traditional_image']['name'] != '' &&
                    is_file($_FILES['traditional_image']['tmp_name'])
                    ) {
                    $fileName = $GLOBALS['db']->baseDir  . DIRECTORY_SEPARATOR . 'tmp'.DIRECTORY_SEPARATOR.$_FILES['traditional_image']['name'];
                    if (move_uploaded_file($_FILES['traditional_image']['tmp_name'], $fileName)) {
                        $taxaImage = $taxaImageColl->addItem();
                        $taxaImage->moveInsert($fileName);
                    }
            }
         
         if (
                 array_key_exists('children_dico_item_id', $_REQUEST) && is_numeric($_REQUEST['children_dico_item_id']) &&
                 array_key_exists('children_dico_id', $_REQUEST) && is_numeric($_REQUEST['children_dico_id'])
             ) {
             $dicoItem = new flora\dico\DicoItem($GLOBALS['db']);
             $dicoItem->loadFromIdAndTaxa($_REQUEST['children_dico_id'],$_REQUEST['children_dico_item_id']);
             $dicoItem->setData($taxa->getData('id'), 'taxa_id');
             $dicoItem->replace();
         }
         $log = new \log\Log($GLOBALS['db']);
         $log->add(
                 $GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa&action=edit&id='.$taxa->getData('id'),
                 $action,
                 $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name')
                );
         if (array_key_exists('children_dico_id', $_REQUEST)) {
            header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=dico&action=edit&id='.$_REQUEST['children_dico_id']);
         } else if (array_key_exists('submit_create_key', $_REQUEST) && array_key_exists('dico_id', $_REQUEST)) {
            header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=dico&action=edit&id='.$_REQUEST['dico_id']);
         } else if (array_key_exists('submit_back', $_REQUEST) ){
            header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa');
         } else {
            header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa&action=edit&id='.$taxa->getData('id'));
         }
         exit(); 
      }
   }
   break; 
case 'delete' :
   $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $taxa->loadFromId($_REQUEST['id']);
      $log = new \log\Log($GLOBALS['db']);
      $log->add(
                 $GLOBALS['db']->config->baseUrl.'administrator.php?task=taxa&action=edit&id='.$taxa->getData('id'),
                 'Cancella',
                 $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name')
                );
      $taxa->delete();
   }
   exit;
   break;
case 'taxaattributelist' :
   $excludeAttributes = array();
   if (array_key_exists('attribute_name_list',$_REQUEST) && $_REQUEST['attribute_name_list'] != '') {
      parse_str ($_REQUEST['attribute_name_list'],$excludeAttributes);
      $excludeAttributes = $excludeAttributes['attribute_name_list'];
      
   }
   $taxaAttributeColl = new \flora\taxa\TaxaAttributeColl($GLOBALS['db']);
   $taxaAttributeColl->loadAll($_REQUEST);
   $result = array();
   foreach ($taxaAttributeColl->getItems() as $taxaAttribute) {
      if (in_array($taxaAttribute->getData('name'), $excludeAttributes)) {
         continue;
      }
      $result[] = array(
          'label'=>$taxaAttribute->getData('name')
      );
   } 
   header('Content-Type: application/json');
   header('Pragma: cache');
   header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
   header('Cache-Control: max-age=3600, must-revalidate, public ');
   echo json_encode($result);
   exit;
   break;
case 'taxaattributelistvalue' :
   $result = array();
   if (array_key_exists('name', $_REQUEST) && $_REQUEST['name'] != '') {
      $taxaAttribute = new \flora\taxa\TaxaAttribute($GLOBALS['db']);
      $taxaAttribute->loadFromName($_REQUEST['name']);
      foreach ($taxaAttribute->getValues($_REQUEST) as $taxaAttributeValue) {
         $result[] = array(
             'label'=>$taxaAttributeValue['value']
         );
      } 
   }
   header('Content-Type: application/json');
   header('Pragma: cache');
   header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
   header('Cache-Control: max-age=3600, must-revalidate, public ');
   echo json_encode($result);
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
   header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
   break;
case 'parse_markup':
   require $GLOBALS['db']->baseDir.'lib/parsedown/Parsedown.php';
   echo Parsedown::instance()->parse($_REQUEST['description_markup']);
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

   $targetDir = $GLOBALS['db']->baseDir  . DIRECTORY_SEPARATOR . 'tmp';
   
   if (!is_dir($targetDir)) {
      mkdir($targetDir);
   }
   $cleanupTargetDir = true; // Remove old files
   $maxFileAge = 5 * 3600; // Temp file age in seconds
   if (!file_exists($targetDir)) {
           @mkdir($targetDir);
   }
   if (!is_dir($targetDir)) {
      $m->error->code = 105;
      $m->error->message = 'Directory temporanea non accessibile in scrittura '.$targetDir;
      echo json_encode($m);
      exit;
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
   $m->result = $fileName;
   echo json_encode($m);
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/taxa/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/taxa/footer.phtml');  
break;
}