<?php
ini_set('include_path','.:/usr/share/php/vendor/zendframework/zendframework/library');
$configArray =array(
    'database' => array(
         'driver' => 'Mysqli',
         'hostname' => 'localhost',
         'username' => 'root',
         'password' => '',
         'database' => 'flora',
         'options' => array('buffer_results' => true)
    ),
    'template' => 'bamboo/1col.phtml',
    'firePHPpath' => '/usr/share/php/vendor/firephp/firephp-core/lib/FirePHPCore/',
    'cache' => array(
         'adapter' => array(
            'name' => 'filesystem'
        ),
        'options' => array(
            'ttl' => 36000,
            'namespace'=>'abbrevia'
        ),
        'plugins' => array('serializer')
    ),
);