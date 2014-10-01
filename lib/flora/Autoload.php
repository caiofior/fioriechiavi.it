<?php
namespace flora;
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

       if (!class_exists('login\Autoload')) {
           require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'login'.DIRECTORY_SEPARATOR.'Autoload.php';
           \login\Autoload::getInstance();

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

      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'Dico.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoItem.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoItemColl.php';
      
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'Taxa.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaKind.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaKindColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaAttribute.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaAttributeColl.php';
      
      require __DIR__.DIRECTORY_SEPARATOR.'region'.DIRECTORY_SEPARATOR.'Region.php';
      require __DIR__.DIRECTORY_SEPARATOR.'region'.DIRECTORY_SEPARATOR.'RegionColl.php';

      return self::$instance;
   }
}
