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
                  $html = new \Zend\Mime\Part(<<<EOT
<p>Messaggio dal sito {$GLOBALS['db']->config->siteName} da parte di {$_REQUEST['name']}<br/>
Mail: <a href="mailto:{$_REQUEST['mail']}">{$_REQUEST['mail']}</a><br/>
Telefono: {$_REQUEST['phone']}<br/>
Fax: {$_REQUEST['fax']}<br/>
</p>
<p>{$_REQUEST['message']}</p>
EOT
                  );
                  $html->type = 'text/html';

                  $body = new \Zend\Mime\Message();
                  $body->setParts(array($html));

                  $message = new \Zend\Mail\Message();
                  $message
                     ->addTo($GLOBALS['config']->mail_from)
                     ->addFrom($GLOBALS['config']->mail_from)
                     ->setSubject('Messaggio dal sito'.$GLOBALS['config']->siteName)
                     ->setBody($body);
                  $GLOBALS['transport']->send($message);

                  $html = new \Zend\Mime\Part(<<<EOT
<p>Hai inviato il seguente messaggio al sito {$GLOBALS['db']->config->siteName}</p>
<p>{$_REQUEST['message']}</p>
<p>A breve verrai contattato.</p>
EOT
                  );
                  $html->type = 'text/html';

                  $body = new \Zend\Mime\Message();
                  $body->setParts(array($html));

                  $message = new \Zend\Mail\Message();
                  $message
                     ->addTo($_REQUEST['mail'])
                     ->addFrom($GLOBALS['config']->mail_from)
                     ->setSubject('Messaggio al sito'.$GLOBALS['config']->siteName)
                     ->setBody($body);
                  $GLOBALS['transport']->send($message);
               } catch (\Exception $e) {
                  $this->getTemplate()->setBlock('middle','contact/errore.phtml');  
               }
               
            }
         }
      }
   break;
}


