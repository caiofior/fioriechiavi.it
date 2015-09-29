<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
require_once __DIR__.'/../lib/facebook-php-sdk-v4/src/Facebook/autoload.php';
$fb = new login\user\Facebook($GLOBALS['db']);
$fb->loadFromId($argv[1]);
$session = new Facebook\Facebook(array(
            'app_id' => $GLOBALS['db']->config->facebook->appId,
            'app_secret' => $GLOBALS['db']->config->facebook->appSecret,
            'default_graph_version' => 'v2.2',
            'default_access_token' =>$fb->getData('accessToken')
));
$GLOBALS['profile'] = $fb->getProfile();
$GLOBALS['association'] = array (
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
$GLOBALS['profile']->update();
ob_start();
require $GLOBALS['db']->baseDir.DIRECTORY_SEPARATOR.'mail'.DIRECTORY_SEPARATOR.'new_user.php';
$html = new \Zend\Mime\Part(ob_get_clean());
$html->type = 'text/html';

$body = new \Zend\Mime\Message();
$body->setParts(array($html));

$message = new \Zend\Mail\Message();
$message
   ->addTo($GLOBALS['profile']->getData('email'))
   ->addFrom($GLOBALS['config']->mail_from)
   ->setSubject('Nuovo utente sul sito '.$GLOBALS['config']->siteName)
   ->setBody($body);
$GLOBALS['transport']->send($message);