<?php
namespace floraobservation;
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

       if (!class_exists('flora\Autoload')) {
           require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'flora'.DIRECTORY_SEPARATOR.'Autoload.php';
           \flora\Autoload::getInstance();
       }

   }
   public static function getInstance()
   {
      if(self::$instance == null)
      {   
         $class = __CLASS__;
         self::$instance = new $class();
      }

      require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'geoPHP'.DIRECTORY_SEPARATOR.'geoPHP.inc';
      require __DIR__.DIRECTORY_SEPARATOR.'TaxaObservation.php';
      require __DIR__.DIRECTORY_SEPARATOR.'TaxaObservationColl.php';
      
      require __DIR__.DIRECTORY_SEPARATOR.'TaxaObservationImage.php';
      require __DIR__.DIRECTORY_SEPARATOR.'TaxaObservationImageColl.php';
      return self::$instance;
   }
}
