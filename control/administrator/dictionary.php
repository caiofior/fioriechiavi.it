<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $termColl = new \dictionary\TermColl($GLOBALS['db']);
      $termColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$termColl->countAll($request);
      $result['iTotalDisplayRecords']=$termColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $termColl->getColumns();
      foreach($termColl->getItems() as $key => $term) {
         $row=array();
         foreach($columns as $column) {
            $data = $term->getRawData($column);
            if ($column == 'description') {
               $data = strip_tags($data);
               if (strlen($data)>200)
                  $data = substr($data,0,200).'&#133;';
            } else if ($column == 'taxa_kind_id') {
               $data=$term->getRawData('taxa_kind_initials');
            } else if ($column == 'actions') {

               $data = '
                   <a class="actions modify" title="Modifica" href="?task=dictionary&amp;action=edit&amp;id='.$term->getData('id').'">Modifica</a>
                   <a class="actions delete" title="Cancella" href="?task=dictionary&amp;action=delete&amp;id='.$term->getData('id').'">Cancella</a>
                   ';
                   if ($term->getRawData('status') == true) {
                   $data .= '
                                          <a class="actions view blank" title="Modifica" href="'.$GLOBALS['db']->config->baseUrl.'?id='.$term->getData('id').'">Visualizza</a>
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
   $this->getTemplate()->setBlock('middle','administrator/dictionary/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dictionary/footer.phtml');
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST) ||
            array_key_exists('submit_back', $_REQUEST) ||
            array_key_exists('submit_create_key', $_REQUEST)
      ) {
      if (!array_key_exists('term', $_REQUEST) ||$_REQUEST['term']=='') {
          $this->addValidationMessage('term','Il termine è obbligatorio');
      }
      if (
              (
              array_key_exists('submit', $_REQUEST) ||
              array_key_exists('submit_back', $_REQUEST) ||
              array_key_exists('submit_create_key', $_REQUEST)
              ) && $this->formIsValid()) {

         

         $term = new \dictionary\Term($GLOBALS['db']);
         
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $term->loadFromId($_REQUEST['id']);
         }
         $term->setData($_REQUEST);
         
            $action='Crea';
            $secondUpdate = false;
            if (!array_key_exists('id', $_REQUEST) || !is_numeric($_REQUEST['id'])) {
               $term->insert();
            } else {
               $term->update();
               $action='Modifica';
               $secondUpdate = true;
            }



            $termImageColl = $term->getTermImageColl();
            if (
                  array_key_exists('image_id_list', $_REQUEST) &&
                  is_array($_REQUEST['image_id_list'])
               ) {

               $idc = $termImageColl->getFieldsAsArray('id');
               $termImage = new \dictionary\TermImage($GLOBALS['db']);
               foreach(array_diff($idc,$_REQUEST['image_id_list']) as $id) {
                  $termImage->loadFromId($id);
                  $termImage->delete();
               }

            }
           $termImageColl = $term->getTermImageColl();


            if (
               array_key_exists('image_name_list', $_REQUEST) &&
               is_array($_REQUEST['image_name_list'])
               ) {
                   foreach ($_REQUEST['image_name_list'] as $imageName) {
                      if (
                              $imageName == '' ||
                              !is_file($GLOBALS['db']->baseDir  . DIRECTORY_SEPARATOR . 'tmp'.DIRECTORY_SEPARATOR.$imageName)
                              ) continue;
                      $termImage = $termImageColl->addItem();
                      $termImage->moveInsert($GLOBALS['db']->baseDir  . DIRECTORY_SEPARATOR . 'tmp'.DIRECTORY_SEPARATOR.$imageName);
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
                           $termImage = $termImageColl->addItem();
                           $termImage->moveInsert($fileName);
                       }
               }

            $log = new \log\Log($GLOBALS['db']);
            $log->add(
                    $GLOBALS['db']->config->baseUrl.'administrator.php?task=dictionary&action=edit&id='.$term->getData('id'),
                    $action,
                    $term->getData('term')
                   );
         
         if (array_key_exists('submit_back', $_REQUEST) ){
            header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=dictionary');
         } else {
            header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=dictionary&action=edit&id='.$term->getData('id'));
         }
         exit();
      }
   }
   break;
case 'delete' :
   $term = new \dictionary\Term($GLOBALS['db']);
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $term->loadFromId($_REQUEST['id']);
      $log = new \log\Log($GLOBALS['db']);
      $log->add(
                 $GLOBALS['db']->config->baseUrl.'administrator.php?task=dictionary&action=edit&id='.$term->getData('id'),
                 'Cancella',
                 $term->getData('term')
                );
      $term->delete();
   }
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
   $this->getTemplate()->setBlock('middle','administrator/dictionary/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/dictionary/footer.phtml');
break;
}
