<?php
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
if (array_key_exists('id', $_REQUEST)) {
   $taxa->loadFromId($_REQUEST['id']);
} else {
   $taxa->loadRoot();
}
if (array_key_exists('id', $_REQUEST) && $taxa->getRawData('status') == 0) {
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $GLOBALS['config']->baseUrl.'/undefined_location.html');
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($handle);
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    echo $response;
    exit;
}
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
   case 'preview':
      header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'general'.DIRECTORY_SEPARATOR.'preview.php';
      exit;
      exit;
   case 'signalObservation':
       if(sizeof($_POST)>0) {
           $taxaObservationColl = $taxa->getTaxaObservationColl();
           $taxaObservation = $taxaObservationColl->addItem();
           if (!(
                   $_REQUEST['title'] == '' ||
                   $_REQUEST['latitude'] == '' ||
                   $_REQUEST['longitude'] == '' ||
                   !is_array($_FILES) ||
                   !array_key_exists('image', $_FILES) ||
                   !is_array($_FILES['image'])
               )) {
            $taxaObservation->setData(strip_tags($_REQUEST['title']),'title');

            require $GLOBALS['db']->baseDir.'lib/parsedown/Parsedown.php';
            $taxaObservation->setData(Parsedown::instance()->line(strip_tags($_REQUEST['description'])),'description');
            $taxaObservation->setData($GLOBALS['profile']->getData('id'),'profile_id');
            $taxaObservation->setData(0,'valid');
            $taxaObservation->setPoint(new \Point(floatval($_REQUEST['longitude']),floatval($_REQUEST['latitude'])));
            $taxaObservation->insert();
            $taxaObservationImageColl = $taxaObservation->getTaxaObservationImageColl();
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
                throw new Exception('Unable to create temporary dir');
            }
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
               throw new Exception('Unable to open temporary dir');
            }
            while (($file = readdir($dir)) !== false) {
                   $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                   if ((filemtime($tmpfilePath) < time() - $maxFileAge)) {
                           @unlink($tmpfilePath);
                   }
            }
            closedir($dir);
            foreach($_FILES['image']['tmp_name'] as $key => $tmpName) {
                $targetFile = $targetDir.DIRECTORY_SEPARATOR;
                $targetFile .= pathinfo($tmpName, PATHINFO_FILENAME).'.';
                $targetFile .= pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION);
                move_uploaded_file($tmpName, $targetFile);
                $taxaObservationImage = $taxaObservationImageColl->addItem();
                $taxaObservationImage->moveInsert($targetFile);
            }
           }

           try{
                  ob_start();
                  require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_observation.php';
                  $GLOBALS['mail']->msgHTML(ob_get_clean());
                  $GLOBALS['mail']->Subject = 'Nuova osservazionione sul sito'.$GLOBALS['config']->siteName;
                  $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->addAddress($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->send();

                  ob_start();
                  require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_observation.php';
                  $GLOBALS['mail']->msgHTML(ob_get_clean());
                  $GLOBALS['mail']->Subject = 'Nuova osservazionione sul sito'.$GLOBALS['config']->siteName;
                  $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->addAddress($GLOBALS['profile']->getData('email'), $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->send();

           } catch (\Exception $e) {}
           header('Location: '.$GLOBALS['db']->config->baseUrl.'index.php?id='.$taxa->getData('id').'&insertObservation=1');
           exit;
       } else {
          header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
          require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'general'.DIRECTORY_SEPARATOR.'signalObservation.phtml';
          exit;
       }
   break;
   case 'taxasearch':
      $taxaColl = new \flora\taxa\TaxaColl($GLOBALS['db']);
      $_REQUEST['iDisplayStart']=0;
      $_REQUEST['iDisplayLength']=10;
      $_REQUEST['status']=1;
      $taxaColl->loadAll($_REQUEST);
      $result = array();
      foreach ($taxaColl->getItems() as $taxa) {
         $result[] = array(
             'label'=>$taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name'),
             'value'=>$GLOBALS['config']->baseUrl.'index.php?id='.$taxa->getData('id')
         );
      }
      header('Content-Type: application/json');
      header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
      echo json_encode($result);
      exit;
   break;
   case 'saveGoogleSearch':
      $googleSearchResult = json_decode(html_entity_decode(file_get_contents('php://input')),true);
      if (is_null($googleSearchResult)) {
         var_dump(json_last_error_msg());
      }
      if (is_array($googleSearchResult)) {
         $GLOBALS['db']->query('DELETE FROM `google_search` WHERE `taxa_id`='.intval($_GET['id']), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
         foreach ($googleSearchResult as $item) {
            $GLOBALS['db']->query('INSERT INTO `google_search` SET
               `thumbnail`= "'.$item['pagemap']['cse_thumbnail'][0]['src'].'",
               `title`= "'.$item['title'].'",
               `link`= "'.$item['link'].'",
               `datetime`=NOW(),
               `taxa_id`='.intval($_GET['id']), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

         }
      }
      exit;
   break;
}
$this->getTemplate()->setObjectData($taxa);
$this->getTemplate()->setBlock('middle','general/index.phtml');
$fileName = 'general/index_'.dirname($this->getTemplate()->getTemplate()).'.phtml';
$filePath = 'view/'.$fileName;
if(is_file($GLOBALS['db']->baseDir.$filePath)) {
    $this->getTemplate()->setBlock('middle',$fileName);
}
