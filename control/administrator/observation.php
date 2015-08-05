<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $taxaObservationColl = new \floraobservation\TaxaObservationColl($GLOBALS['db']);
      if($GLOBALS['profile']->getData('role_id')) {
          $_REQUEST['profile_id']=$GLOBALS['profile']->getData('id');
      }
      $taxaObservationColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$taxaObservationColl->countAll($request);
      $result['iTotalDisplayRecords']=$taxaObservationColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $taxaObservationColl->getColumns();
      foreach($taxaObservationColl->getItems() as $key => $taxaObservation) {
         $row=array();
         foreach($columns as $column) {
            $data = $taxaObservation->getRawData($column);
            $vector_size = mcrypt_get_iv_size($GLOBALS['db']->config->crypt->cipher,$GLOBALS['db']->config->crypt->mode);
            $cid = urlencode(mcrypt_encrypt(
                    $GLOBALS['db']->config->crypt->cipher,
                    $GLOBALS['db']->config->crypt->key,
                    $GLOBALS['db']->config->crypt->seed.$taxaObservation->getData('id'),
                    $GLOBALS['db']->config->crypt->mode,substr($GLOBALS['db']->config->crypt->iv,0,$vector_size)
            ));
            if ($column == 'actions') {
               $data = '<a class="actions modify" title="Modifica" href="?task=observation&amp;action=edit&amp;cid='.$cid.'">Modifica</a>
                   <a class="actions delete" title="Cancella" href="?task=observation&amp;action=delete&amp;cid='.$cid.'">Cancella</a>';
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
   $this->getTemplate()->setBlock('middle','administrator/observation/edit.phtml');
   $this->getTemplate()->setBlock('footer','administrator/observation/footer.phtml');  
   if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
      ) {
      if (!array_key_exists('title', $_REQUEST) ||$_REQUEST['title']=='') {
          $this->addValidationMessage('title','Il titolo è obbligatorio');
      }
      if (!array_key_exists('description', $_REQUEST) ||$_REQUEST['description']=='') {
          $this->addValidationMessage('description','La descrizione è obbligatoria');
      }
      if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
         $taxaObservation = new \floraobservation\TaxaObservation($GLOBALS['db']);
         
         if (array_key_exists('cid', $_REQUEST)) {
            $vector_size = mcrypt_get_iv_size($GLOBALS['db']->config->crypt->cipher,$GLOBALS['db']->config->crypt->mode);
            $_REQUEST['id'] = mcrypt_decrypt(
                    $GLOBALS['db']->config->crypt->cipher,
                    $GLOBALS['db']->config->crypt->key,
                    $_REQUEST['cid'],
                    $GLOBALS['db']->config->crypt->mode,
                    substr($GLOBALS['db']->config->crypt->iv,0,$vector_size)
                    );
            $_REQUEST['id']=substr($_REQUEST['id'],strlen($GLOBALS['db']->config->crypt->seed));
         }
         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxaObservation->loadFromId($_REQUEST['id']);
         }
         $taxaObservation->setData($_REQUEST);
         $taxaObservation->setData((array_key_exists('valid', $_REQUEST)?1:0),'valid');

         if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
            $taxaObservation->update();
         } else {
            $taxaObservation->insert();
         }
         header('Location: '.$GLOBALS['db']->config->baseUrl.'administrator.php?task=observation');
         exit(); 
      }
   }
   break; 
case 'delete' :
   $taxaObservation = new \floraobservation\TaxaObservation($GLOBALS['db']);
   if (array_key_exists('cid', $_REQUEST)) {
    $vector_size = mcrypt_get_iv_size($GLOBALS['db']->config->crypt->cipher,$GLOBALS['db']->config->crypt->mode);
    $_REQUEST['id'] = mcrypt_decrypt(
            $GLOBALS['db']->config->crypt->cipher,
            $GLOBALS['db']->config->crypt->key,
            $_REQUEST['cid'],
            $GLOBALS['db']->config->crypt->mode,
            substr($GLOBALS['db']->config->crypt->iv,0,$vector_size)
            );
    $_REQUEST['id']=substr($_REQUEST['id'],strlen($GLOBALS['db']->config->crypt->seed));
   }
   if (array_key_exists('id', $_REQUEST) && is_numeric($_REQUEST['id'])) {
      $taxaObservation->loadFromId($_REQUEST['id']);
      $taxaObservation->delete();
   }
   exit;
   break;
default:
   $this->getTemplate()->setBlock('middle','administrator/observation/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/observation/footer.phtml');  
break;
}