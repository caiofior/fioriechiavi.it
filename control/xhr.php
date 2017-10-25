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
   case 'taxa':
      switch ($_REQUEST['action']) {
         case 'search':
            $taxaId = null;
            if (
                    array_key_exists('eol_id', $_REQUEST) &&
                    $_REQUEST['eol_id'] != ''
                  ) {
               $resultSet = $GLOBALS['db']->query('SELECT `id` FROM `taxa` 
                WHERE `eol_id`=' . intval($_REQUEST['eol_id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
               $resultSet = $resultSet->toArray();
               if (sizeof($resultSet)>0) {
                  $taxaId = current(current($resultSet));
               }
            }
            if (
                    $taxaId == null &&
                    array_key_exists('eol_id', $_REQUEST) &&
                    $_REQUEST['eol_id'] != ''
                  ) {
               $resultSet = $GLOBALS['db']->query('SELECT `id` FROM `taxa` 
                WHERE `col_id`="' . addslashes($_REQUEST['col_id']).'"'
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
               $resultSet = $resultSet->toArray();
               if (sizeof($resultSet)>0) {
                  $taxaId = current(current($resultSet));
               }
            }
            if (
                    $taxaId == null &&
                    array_key_exists('name', $_REQUEST) &&
                    $_REQUEST['name'] != ''
                  ) {
               $resultSet = $GLOBALS['db']->query('SELECT `id` FROM `taxa` 
                WHERE `name` LIKE "%' . addslashes($_REQUEST['name']).'%"'
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
               $resultSet = $resultSet->toArray();
               if (sizeof($resultSet)>0) {
                  $taxaId = current(current($resultSet));
               }
            }
            if (is_null($taxaId)) {
               echo json_encode(false);
            } else {
               echo json_encode($GLOBALS['db']->config->baseUrl.'index.php?id='.intval($taxaId));
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
                  if (is_array($file)) {
                     $observationData = json_decode(file_get_contents($file['tmp_name']),true);
                     if (
                             array_key_exists('taxa_id',$observationData) &&
                             array_key_exists('title',$observationData) &&
                             array_key_exists('description',$observationData) &&
                             array_key_exists('latitude',$observationData) &&
                             array_key_exists('longitude',$observationData) &&
                             array_key_exists('datetime',$observationData)
                             ) {
                        
                        try {
                           $dateTime = new \DateTime($observationData['datetime']);
                        } catch (\Exception $e) {
                           $dateTime = date_create_from_format ('m/d/Y\+g:i:s\+a+',$observationData['datetime']);
                        }

                        $taxa = new \flora\taxa\Taxa($GLOBALS['db']);
                        $taxa->loadFromId($observationData['taxa_id']);
                        $taxaObservationColl = $taxa->getTaxaObservationColl();
                        $taxaObservation = $taxaObservationColl->addItem();
                        $taxaObservation->setData(strip_tags($observationData['title']),'title');
                        $taxaObservation->setData(strip_tags($observationData['description']),'description');
                        $taxaObservation->setData($profile->getData('id'),'profile_id');
						if (is_object($dateTime)) {
                        	$taxaObservation->setData($dateTime->format('Y-m-d H:i:s'),'datetime');
						}
                        $taxaObservation->setData(0,'valid');
                        $taxaObservation->setPoint(new \Point(floatval($observationData['longitude']),floatval($observationData['latitude'])));
                        $taxaObservation->insert();
                        $result->valid = true;
                        $result->id = $taxaObservation->getData('id');
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
         case 'appendimage':
            $result = (object) array('valid' => false);
            file_put_contents('/tmp/request.txt', var_export($_REQUEST,true));
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
                             array_key_exists('id',$_REQUEST)
                             ) {
                        $taxaObservation = new floraobservation\TaxaObservation($GLOBALS['db']);
                        $taxaObservation->loadFromId($_REQUEST['id']);

                        $rawTmpFile = tempnam(sys_get_temp_dir(),'');
                        $imgFileName = null;
                        switch (getimagesize($file['tmp_name'])[2]) {
                           case IMAGETYPE_GIF :
                              $imgFileName = $rawTmpFile.'.gif';
                           break;
                           case IMAGETYPE_JPEG :
                           case IMAGETYPE_JPEG2000 :   
                              $imgFileName = $rawTmpFile.'.jpeg';
                           break;
                           case IMAGETYPE_PNG :
                              $imgFileName = $rawTmpFile.'.png';
                           break;
                           case IMAGETYPE_BMP :
                                IMAGETYPE_WBMP :
                              $imgFileName = $rawTmpFile.'.bmp';
                           break;
                           case IMAGETYPE_TIFF_II  :
                                IMAGETYPE_TIFF_MM  :
                              $imgFileName = $rawTmpFile.'.bmp';
                           break;
                        }
                        file_put_contents('/tmp/file_path.txt',$imgFileName);
                        if (!is_null($imgFileName)) {
                           move_uploaded_file($file['tmp_name'], $imgFileName);
                           $taxaObservationImageColl = $taxaObservation->getTaxaObservationImageColl();
                           $taxaObservationImage = $taxaObservationImageColl->addItem();
                           $taxaObservationImage->moveInsert($imgFileName);
                           $result->valid = true;

                           try{
                                 ob_start();
                                 require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_observation.php';
                                 $GLOBALS['mail']->msgHTML(ob_get_clean());
                                 $GLOBALS['mail']->Subject = 'Nuova osservazione sul sito'.$GLOBALS['config']->siteName;
                                 $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                                 $GLOBALS['mail']->addAddress($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                                 $GLOBALS['mail']->send();

                                 ob_start();
                                 require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_observation.php';
                                 $GLOBALS['mail']->msgHTML(ob_get_clean());
                                 $GLOBALS['mail']->Subject = 'Nuova osservazione sul sito'.$GLOBALS['config']->siteName;
                                 $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                                 $GLOBALS['mail']->addAddress($GLOBALS['profile']->getData('email'), $GLOBALS['config']->siteName);
                                 $GLOBALS['mail']->send();
                                 
                          } catch (\Exception $e) {}





                        } else {
                           unlink($rawTmpFile);
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