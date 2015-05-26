<?php 
   ini_set('display_errors',1);
   ini_set('error_reporting',E_ALL);
   require __DIR__.'/config/config.php';
   require __DIR__.'/include/zendRequireCompiled.php';
  
   
   $config = new \Zend\Config\Config($configArray);
   $GLOBALS['db'] = new \Zend\Db\Adapter\Adapter($config->database->toArray());
   $GLOBALS['db']->baseDir=__DIR__.'/../../../';
   
   require __DIR__.'/lib/floraobservation/Autoload.php';
   \floraobservation\Autoload::getInstance();

   
   $object = new \floraobservation\TaxaObservation($GLOBALS['db']);
   $GLOBALS['db']->query('DELETE FROM `taxa_observation` WHERE `id` = 1' , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   $object->setData(array(
            'id'=>1,
            'taxa_id'=>1,
            'profile_id'=>1,
            'title'=>'Test',
            'description'=>'Test',
            'latitude'=>45.124536,
            'longitude'=>12.458975
        ));
    $object->insert();
    $object->loadFromId(1);
    var_dump($object->getRawData());