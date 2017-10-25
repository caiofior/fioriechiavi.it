<?php
$sessionDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'tmp';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir); 
}
if (!is_dir($sessionDir)) {
    throw new Exception('Unable to create temporary directory '.$sessionDir,1512250906);
}
if (rand(0,10) == 0) {
    if ($handle = opendir($sessionDir)) {
        while (false !== ($file = readdir($handle))) { 
            $filelastmodified = filemtime($sessionDir .DIRECTORY_SEPARATOR. $file);
            if(is_file($sessionDir .DIRECTORY_SEPARATOR. $file) && (time() - $filelastmodified) > 24*60) {
               unlink($sessionDir . DIRECTORY_SEPARATOR . $file);
            }
        }
        closedir($handle); 
    }
}
if (!extension_loaded('openssl') && !extension_loaded('mcrypt')) {
    throw new Exception('Openssl or Mcrypt extension is required',1604061516);
}
if (!extension_loaded('curl')) {
    throw new Exception('cURL extension is required',1604061518);
}
if (!extension_loaded('mysql') && !extension_loaded('mysqli')) {
    throw new Exception('Mysql extension is required',1604061517);
}
session_save_path($sessionDir);
require __DIR__.'/../config/config.php';
//require __DIR__.'/monitoring.php';
if (is_file(__DIR__.'/zendRequireCompiled.php')) {
   require __DIR__.'/zendRequireCompiled.php';
} else if (is_file(__DIR__.'/zendRequire.php')) {
   require __DIR__.'/zendRequire.php';
} else {
require 'vendor/autoload.php';
$loader = new Zend\Loader\StandardAutoloader(array(
    'autoregister_zf' => true,
    'fallback_autoloader' => true
));
$loader->register();
}
require __DIR__.'/../lib/PHPMailer-master/src/PHPMailer.php';
require __DIR__.'/../lib/PHPMailer-master/src/SMTP.php';
require __DIR__.'/../lib/PHPMailer-master/src/Exception.php';
if (!isset($configArray) || !is_array($configArray)) {
    throw new Exception('Config file is wrong',1512250909);
}
$config = new Zend\Config\Config($configArray);
$db = new Zend\Db\Adapter\Adapter($config->database->toArray());
$baktrace = debug_backtrace();
if (sizeof($baktrace) < 1)
   throw new Exception ('No backtrace available to create base path', 0710141057);
$db->baseDir = dirname($baktrace[0]['file']).DIRECTORY_SEPARATOR;
$db->baseDir = str_replace('/shell/', '/', $db->baseDir);
try{  
$db->cache = Zend\Cache\StorageFactory::factory($config->cache->toArray());
} catch (\Exception  $e) {
   if(preg_match('/Cache directory \'(.*)\' not found or not a directory/',$e->getMessage(),$catches)){
      mkdir($catches[1]);
      $db->cache = Zend\Cache\StorageFactory::factory($config->cache->toArray());
   } else {
      throw $e;
   }   
}
$db->config = $config;
 
require __DIR__.'/../lib/floraobservation/Autoload.php';
floraobservation\Autoload::getInstance();

$template = new Template(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR,$config->template);
$template->setBlock('head','general/head.phtml');
$template->setBlock('header','general/header.phtml');
$template->setBlock('breadcrumbs','general/breadcrumbs.phtml');
$template->setBlock('navigation','general/navigation.phtml');
$template->setBlock('footer','general/footer.phtml');
$control = $template->createControl();
$control->setBaseDir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'control');
$connected = true;
set_error_handler(create_function('', ''));
try{
$db->getDriver()->getConnection()->connect();
} catch (\Exception $e) {
   $connected = false;
}
restore_error_handler();
if (!$connected) {
   switch(php_sapi_name()) {
   case 'cli':
        throw new Exception('Db connection error',2404150924);
   break;
   default:
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 300');
        $GLOBALS['user']=null;
        $control->addValidationMessage('error','errore nelle nostre macchine');
        $template->setBlock('middle','error/middle.phtml');
        $template->setBlock('navigation','error/navigation.phtml');
        $template->render();
   break;
   }
   exit;
}
if ($config->mail_from == '') {
   throw new \Exception('Sender email is required',1409011411);
}
if(!is_null($config->smtp)) {
   
   $mail = new \PHPMailer\PHPMailer\PHPMailer();
   $mail->SMTPOptions = array(
       'ssl' => array(
           'verify_peer' => false,
           'verify_peer_name' => false,
           'allow_self_signed' => true
       )
   );
   $mail->isSMTP();
   
   $mail->Host = $config->smtp->host;
   $mail->Port = $config->smtp->port;
   $mail->SMTPSecure = $config->smtp->connection_config->ssl;

   $mail->SMTPAuth = $config->smtp->connection_class=='login';

   $mail->Username = $config->smtp->connection_config->username;
   $mail->Password = $config->smtp->connection_config->password;

}
if (PHP_SAPI != 'cli') {
   require 'session.php';
}
if (array_key_exists('autocomplete', $_GET) && array_key_exists('domain', $_GET)) {
   if (!array_key_exists('term', $_GET))
      $_GET['term']=null;     
   $providerColl = new \abbrevia\domain\DomainColl($db);
   $providerColl->loadAll(array('cod_dominio'=>$_GET['domain'],'sSearch'=>$_GET['term']));
   $result=array();
   foreach($providerColl->getItems() as $item) {
      $result[] = array(
          'label'=>$item->getData($_GET['col'][1]),
          'id'=>$item->getData($_GET['col'][0])
              );
   } 
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
}

