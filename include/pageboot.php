<?php
require __DIR__.'/../config/config.php';
ini_set('error_reporting',E_ALL);
require 'Zend/Loader/StandardAutoloader.php';
$loader = new Zend\Loader\StandardAutoloader(array(
    'autoregister_zf' => true,
    'fallback_autoloader' => true
));
$loader->register();

$config = new Zend\Config\Config($configArray);
$db = new Zend\Db\Adapter\Adapter($config->database->toArray());
$db->baseDir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public').DIRECTORY_SEPARATOR;
$db->cache = Zend\Cache\StorageFactory::factory($config->cache->toArray());
$db->config = $config;

require __DIR__.'/../lib/flora/Autoload.php';
abbrevia\Autoload::getInstance();

$template = new Template(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR,$config->template);
$template->setBlock('head','general/head.phtml');
$template->setBlock('header','general/header.phtml');
$template->setBlock('breadcrumbs','general/breadcrumbs.phtml');
$template->setBlock('navigation','general/navigation.phtml');
$template->setBlock('footer','general/footer.phtml');
$control = $template->createControl();
$control->setBaseDir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'control');

if ($config->mail_from == '') {
   throw new \Exception('Sender email is required',1409011411);
}
if(!is_null($config->smtp)) {
   $transport = new Zend\Mail\Transport\Smtp();
   $transport->setOptions(new Zend\Mail\Transport\SmtpOptions($config->smtp->toArray()));
} else {
   $transport = new Zend\Mail\Transport\Sendmail();
}
require 'session.php';
if (key_exists('autocomplete', $_GET) && key_exists('domain', $_GET)) {
   if (!key_exists('term', $_GET))
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

