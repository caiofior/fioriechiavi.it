<?php
namespace login;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Autoload
 *
 * @author caiofior
 */
class Autoload {
   private static $instance = null;
   private function __construct() {
      if (!class_exists('Autoload')) {
         require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'Autoload.php';
         \Autoload::getInstance();
      
      }
 
      $firePhpDir = $GLOBALS['db']->baseDir.'/lib/firephp/firephp-core/lib/FirePHPCore';
      if (!class_exists('FirePHP') && is_dir($firePhpDir)) {
         require $firePhpDir.'/FirePHP.class.php';
         require $firePhpDir.'/fb.php';
         $GLOBALS['firephp'] = \FirePHP::getInstance(true);
      } 
   }
   public static function getInstance()
   {
      if(self::$instance == null)
      {   
         $class = __CLASS__;
         self::$instance = new $class();
      }
      require __DIR__ . DIRECTORY_SEPARATOR . 'Auth.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'LoginInstantiator.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'Login.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'LoginColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'Facebook.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'Profile.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'ProfileColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'ProfileRole.php';

      return self::$instance;
   }
}
