<?php

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
      if (class_exists('Template')) return;
      require __DIR__.DIRECTORY_SEPARATOR.'Template.php';
      require __DIR__.DIRECTORY_SEPARATOR.'Content.php';
      require __DIR__.DIRECTORY_SEPARATOR.'ContentColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'Control.php';
   }
   public static function getInstance()
   {
      if(self::$instance == null)
      {   
         $class = __CLASS__;
         self::$instance = new $class;
      }
      return self::$instance;
   }
}
