<?php
require __DIR__.'/../include/pageboot.php';
require __DIR__.'/../lib/facebook/Facebook/autoload.php';
$fb = new login\user\Facebook($db);
$fb->loadFromId($argv[1]);
$session = new Facebook\Facebook(array(
            'app_id' => $db->config->facebook->appId,
            'app_secret' => $db->config->facebook->appSecret,
            'default_graph_version' => 'v2.2',
            'default_access_token' =>$fb->getData('accessToken')
));
$profile = $fb->getProfile();
$association = array (
    'first_name'=>'first_name',
    'last_name'=>'last_name',
    'email'=>'email'
);
$user_profile = $session->get('/me')->getGraphUser()->asArray();
array_walk_recursive($user_profile, function($val,$index,$fb) {
        if ($val instanceof DateTime) {
            $val = $val->format('Y-m-d H:i:s');
        }
        if (
                array_key_exists($index, $GLOBALS['association']) &&
                $GLOBALS['profile']->getData($GLOBALS['association'][$index]) == ''
           ) {
            $GLOBALS['profile']->setData($val,$GLOBALS['association'][$index]);
           }
        $fb->setGraphvalue($index,$val);
    }
,$fb);
$profile->update();
ob_start();
require $db->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_user.php';
$html = new \Zend\Mime\Part(ob_get_clean());
$html->type = 'text/html';

$body = new \Zend\Mime\Message();
$body->setParts(array($html));

$message = new \Zend\Mail\Message();
$message
   ->addTo($login)
   ->addFrom($GLOBALS['config']->mail_from)
   ->setSubject('Nuovo utente sul sito '.$GLOBALS['config']->siteName)
   ->setBody($body);
$GLOBALS['transport']->send($message);