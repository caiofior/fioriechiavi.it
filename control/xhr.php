<?php
if (!array_key_exists('task', $_REQUEST))
   $_REQUEST['task']=null;
if (!array_key_exists('action', $_REQUEST))
         $_REQUEST['action']=null;
switch ($_REQUEST['task']) {
   case 'user':
      switch ($_REQUEST['action']) {      
         case 'login':
            $result = (object) array('valid' => false);
            $auth = new Zend\Authentication\AuthenticationService();
            if (
                    array_key_exists('username', $_REQUEST) &&
                    array_key_exists('password', $_REQUEST)        
                    ) {
            $authAdapter = new \login\Auth($GLOBALS['db'],$_REQUEST['username'], $_REQUEST['password']);
            $authResult = $auth->authenticate($authAdapter);
            if ($authResult->getCode() == \Zend\Authentication\Result::SUCCESS) {
               $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'],$auth->getStorage()->read());
               $result = (object) $user->getData();
               unset($result->password,$result->confirm_code,$result->new_username,$result->change_datetime,$result->confirm_datetime,$result->profile_id);
               $result->valid = true;
               $result->token = $user->getProfile()->getData('token');
                    }
                    
            }
            if (array_key_exists('callback', $_REQUEST)) {
               echo $_REQUEST['callback'].'('.json_encode( $result ).')';
            } else {
               echo json_encode( $result );
            }
         break;
         case 'recover':
            $result = (object) array('valid' => false);
            if (
                    array_key_exists('usernameRecover', $_REQUEST) &&
                    $_REQUEST['usernameRecover']!='' && 
                    filter_var($_REQUEST['usernameRecover'], FILTER_VALIDATE_EMAIL)      
                    ) {
               $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'], $_REQUEST['usernameRecover']);
               if(is_object($user)) {
                  $result = (object) array('valid' => true);
                  $user->resetPassword();
              }
            }
            
            if (array_key_exists('callback', $_REQUEST)) {
                  echo $_REQUEST['callback'].'('.json_encode( $result ).')';
               } else {
                  echo json_encode( $result );
            }   
         break;
         case 'register':
            $result = (object) array('valid' => false);            
            if (
                    filter_var($_REQUEST['username'], FILTER_VALIDATE_EMAIL) &&
                    strlen($_REQUEST['password']) > 3
                  ) {
               $user = \login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'], $_REQUEST['username']);
               if(!is_object($user) || $user->getData('username') == '') {
                  try {
                     $user = \login\user\LoginInstantiator::createLoginInstance($GLOBALS['db'], $_REQUEST['username'],$_REQUEST['password']);
                     $result = (object) $user->getData();
                     unset($result->password,$result->confirm_code,$result->new_username,$result->change_datetime,$result->confirm_datetime,$result->profile_id);
                     $result->valid = true;
                     $result->token = $user->getProfile()->getData('token');
                  } catch (\Exception $e) {}
                  
               }
            }
            if (array_key_exists('callback', $_REQUEST)) {
               echo $_REQUEST['callback'].'('.json_encode( $result ).')';
            } else {
               echo json_encode( $result );
            }
         break;
      }
   break;
   case 'observation':
      switch ($_REQUEST['action']) {
         case 'signal':
            $result = (object) array('valid' => false);
            if (is_array($_FILES)) {
               $auth = new Zend\Authentication\AuthenticationService();
               $authAdapter = new \login\Auth($GLOBALS['db'],null, null,$_REQUEST['token']);
               $authResult = $auth->authenticate($authAdapter);
               if ($authResult->getCode() == \Zend\Authentication\Result::SUCCESS) {
                  $user = login\user\LoginInstantiator::getLoginInstance($GLOBALS['db'],$auth->getStorage()->read());
                  if (is_object($user)) {
                     $profile = $user->getProfile();
                     $file = current($_FILES);
                     if (
                             is_array($file) &&
                             array_key_exists('tmp_name',$file)
                             ) {
                        $observationContent = file_get_contents($file['tmp_name']);
                        $observationData = json_decode($observationContent,true);
                        if (
                             array_key_exists('taxa_id',$observationData) &&
                             array_key_exists('image',$observationData) &&
                             array_key_exists('title',$observationData) &&
                             array_key_exists('description',$observationData) &&
                             array_key_exists('datetime',$observationData)
                             ) {
                           $dateTime = new \DateTime($observationData['datetime']);

                           $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
                           $taxa->loadFromId($observationData['taxa_id']);
                           $taxaObservationColl = $taxa->getTaxaObservationColl();
                           $taxaObservation = $taxaObservationColl->addItem();
                           $taxaObservation->setData(strip_tags($observationData['title']),'title');
                           $taxaObservation->setData(strip_tags($observationData['description']),'description');
                           $taxaObservation->setData($profile->getData('id'),'profile_id');
                           $taxaObservation->setData($dateTime->format('Y-m-d H:i:s'),'datetime');
                           $taxaObservation->setData(0,'valid');
                           $taxaObservation->setPoint(new \Point(floatval($observationData['longitude']),floatval($observationData['latitude'])));
                           $taxaObservation->insert();

                           $rawTmpFile = tempnam(sys_get_temp_dir(),'');
                           $imgFileName = null;
                           switch (getimagesize($rawTmpFile)[2]) {
                              case IMG_GIF :
                                 $imgFileName = $rawTmpFile.'.gif';
                              break;
                              case IMG_JPG :
                                 $imgFileName = $rawTmpFile.'.jpeg';
                              break;
                              case IMG_PNG :
                                 $imgFileName = $rawTmpFile.'.png';
                              break;
                              case IMG_WBMP :
                                 $imgFileName = $rawTmpFile.'.bmp';
                              break;
                           }
                           if (!is_null($imgFileName)) {
                              rename($rawTmpFile, $imgFileName);
                              $taxaObservationImageColl = $taxaObservation->getTaxaObservationImageColl();
                              $taxaObservationImage = $taxaObservationImageColl->addItem();
                              $taxaObservationImage->moveInsert($imgFileName);
                              $result->valid = true;
                              
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
                             } catch (\Exception $e) {}
                              
                              
                              
                              
                              
                           } else {
                              unlink($rawTmpFile);
                           }
                        }
                     }
                  }
               }
            }
            if (array_key_exists('callback', $_REQUEST)) {
                  echo $_REQUEST['callback'].'('.json_encode( $result ).')';
               } else {
                  echo json_encode( $result );
            }  
         break;
      }
   break;
}
exit;