<?php
require __DIR__.'/../include/pageboot.php';
require __DIR__.'/../lib/facebook/Facebook/autoload.php';
$fb = new login\user\Facebook($db);
$fb->loadFromId($argv[1]);
Facebook\FacebookSession::setDefaultApplication($db->config->facebook->appId, $db->config->facebook->appSecret);
$session = new Facebook\FacebookSession($fb->getData('accessToken'));

