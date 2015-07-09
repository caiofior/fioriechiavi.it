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
   case 'signalObservation':
       if(sizeof($_POST)>0) {
           $taxaObservationColl = $taxa->getTaxaObservationColl();
           $taxaObservation = $taxaObservationColl->addItem();
           if (!(
                   $_REQUEST['title'] == '' ||
                   $_REQUEST['description'] == '' ||
                   $_REQUEST['latitude'] == '' ||
                   $_REQUEST['longitude'] == '' ||
                   !is_array($_FILES) ||
                   !array_key_exists('image', $_FILES) ||
                   !is_array($_FILES['image'])
               )) {
            $taxaObservation->setData(strip_tags($_REQUEST['title']),'title');

            require $GLOBALS['db']->baseDir.'lib/parsedown/Parsedown.php';
            $taxaObservation->setData(Parsedown::instance()->parse(strip_tags($_REQUEST['description'])),'description'); 
            $taxaObservation->setData(floatval($_REQUEST['latitude']),'latitude');
            $taxaObservation->setData(floatval($_REQUEST['longitude']),'longitude');
            $taxaObservation->setData($GLOBALS['profile']->getData('id'),'profile_id');
            $taxaObservation->setData(0,'valid');
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
                  $html = new \Zend\Mime\Part(ob_get_clean());
                  $html->type = 'text/html';

                  $body = new \Zend\Mime\Message();
                  $body->setParts(array($html));

                  $message = new \Zend\Mail\Message();
                  $message
                     ->addTo($GLOBALS['config']->mail_from)
                     ->addFrom($GLOBALS['config']->mail_from)
                     ->setSubject('Nuova osservazionione sul sito'.$GLOBALS['config']->siteName)
                     ->setBody($body);
                  $GLOBALS['transport']->send($message);

                  ob_start();
                  require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_observation.php';
                  $html = new \Zend\Mime\Part(ob_get_clean());
                  $html->type = 'text/html';

                  $body = new \Zend\Mime\Message();
                  $body->setParts(array($html));

                  $message = new \Zend\Mail\Message();
                  $message
                     ->addTo($GLOBALS['profile']->getData('email'))
                     ->addFrom($GLOBALS['config']->mail_from)
                     ->setSubject('Nuova osservazionione sul sito'.$GLOBALS['config']->siteName)
                     ->setBody($body);
                  $GLOBALS['transport']->send($message);
           }catch (\Exception $e) {}
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
             'value'=>$GLOBALS['config']->baseUrl.'/index.php?id='.$taxa->getData('id')
         );
      } 
      header('Content-Type: application/json');
      header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 60*60*24));
      echo json_encode($result);
      exit;
   break;
}
$this->getTemplate()->setObjectData($taxa);
$this->getTemplate()->setBlock('middle','general/index.phtml');

