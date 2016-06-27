<?php
require __DIR__.'/include/pageboot.php';
if(!preg_match('/http(s)?:\\/\\/'.$_SERVER['SERVER_NAME'].'/', $GLOBALS['db']->config->baseUrl)) {
   session_destroy();
   foreach ($_COOKIE as $c_id => $c_value)
   {
       setcookie($c_id, NULL, 1, '/', '.'.$_SERVER['SERVER_NAME']);
   }
   header('Location: '.$GLOBALS['db']->config->baseUrl);
   exit;
}
$control->setPage(__FILE__);
$template->render();