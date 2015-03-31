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

$user_profile = $session->get('/me')->getGraphUser()->asArray();
array_walk_recursive($user_profile, function($val,$index,$fb) {
        if ($val instanceof DateTime) {
            $val = $val->format('Y-m-d H:i:s');
        }
        $fb->setGraphvalue($index,$val);
    }
,$fb);