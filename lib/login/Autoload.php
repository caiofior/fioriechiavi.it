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
 
      if (!class_exists('FirePHP') && array_key_exists('config', $GLOBALS) && $GLOBALS['config']->firePHPpath != '') {
         require $GLOBALS['config']->firePHPpath.DIRECTORY_SEPARATOR.'FirePHP.class.php';
         require $GLOBALS['config']->firePHPpath.DIRECTORY_SEPARATOR.'fb.php';
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
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'UserInstantiator.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'User.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'UserColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'Profile.php';

      return self::$instance;
   }
}