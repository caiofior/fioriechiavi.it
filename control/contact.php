<?php
$this->getTemplate()->setBlock('head','contact/head.phtml');
if (!array_key_exists('task', $_REQUEST)) {
   $_REQUEST['task']=null;
}
switch ($_REQUEST['task']) {
   default :
      $this->getTemplate()->setBlock('middle','contact/middle.phtml');
         if (
            array_key_exists('xhrValidate', $_REQUEST) ||
            array_key_exists('submit', $_REQUEST)
         )  {
         if (!array_key_exists('name', $_REQUEST) ||$_REQUEST['name']=='') {
             $this->addValidationMessage('name','Il nome è obbligatorio');
         }
         if (!array_key_exists('mail', $_REQUEST) ||$_REQUEST['mail']=='') {
            $this->addValidationMessage('mail','La mail è obbligatoria');
         } else if (!filter_var($_REQUEST['mail'], FILTER_VALIDATE_EMAIL)) {
            $this->addValidationMessage('mail','La mail non è corretta');
         }
         if (!array_key_exists('message', $_REQUEST) ||$_REQUEST['message']=='') {
             $this->addValidationMessage('message','Il messaggio è obbligatorio');
         }
         if (array_key_exists('submit', $_REQUEST) && $this->formIsValid()) {
            require __DIR__.'/../lib/contact/Autoload.php';
            contact\Autoload::getInstance();
            $_REQUEST['datetime']=date('Y-m-d H:i:s');
            $_REQUEST['ip']=$_SERVER['REMOTE_ADDR'];
            $_REQUEST['message']=  strip_tags($_REQUEST['message']);
            $_REQUEST['recency']=  3600;
            
            $contactColl = new \contact\contact\ContactColl($GLOBALS['db']);
            $recentContacts = $contactColl->countAll($_REQUEST);
            if($recentContacts > 10) {
               $this->getTemplate()->setBlock('middle','contact/spam.phtml');   
            } else {
               sleep($recentContacts*2);
               $contact = new \contact\contact\Contact($GLOBALS['db']);

               $contact->setData($_REQUEST);
               $contact->insert();
               $this->getTemplate()->setBlock('middle','contact/sent.phtml');   
               
               try{
                  ob_start();
                  require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_message.php';
                  
                  $GLOBALS['mail']->msgHTML(ob_get_clean());
                  $GLOBALS['mail']->Subject = 'Messaggio dal sito'.$GLOBALS['config']->siteName;
                  $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->addAddress($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->send();
                  
                  ob_start();
                  require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_message.php';
                  $GLOBALS['mail']->msgHTML(ob_get_clean());
                  $GLOBALS['mail']->Subject = 'Messaggio dal sito'.$GLOBALS['config']->siteName;
                  $GLOBALS['mail']->setFrom($GLOBALS['config']->mail_from, $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->addAddress($_REQUEST['mail'], $GLOBALS['config']->siteName);
                  $GLOBALS['mail']->send();
                  
               } catch (\Exception $e) {
                  $this->getTemplate()->setBlock('middle','contact/error.phtml');  
               }
               
            }
         }
      }
   break;
}


