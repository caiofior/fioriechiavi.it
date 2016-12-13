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
       
       if (!class_exists('content\Autoload')) {
           require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'Autoload.php';
           \content\Autoload::getInstance();
       }
       
      $firePhpDir = $GLOBALS['db']->baseDir.'/lib/firephp/lib/FirePHPCore';
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
      require __DIR__.DIRECTORY_SEPARATOR.'linkprovider'.DIRECTORY_SEPARATOR.'LinkProvider.php';
      require __DIR__.DIRECTORY_SEPARATOR.'linkprovider'.DIRECTORY_SEPARATOR.'LinkProviderColl.php';

      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoInt.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoItemInt.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoItemIntColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoItem.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'DicoItemColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'AddDico.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'AddDicoColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'AddDicoItem.php';
      require __DIR__.DIRECTORY_SEPARATOR.'dico'.DIRECTORY_SEPARATOR.'AddDicoItemColl.php';
      
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'Taxa.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaKind.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaKindColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaAttribute.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaAttributeColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaImage.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaImageColl.php';
      require __DIR__.DIRECTORY_SEPARATOR.'taxa'.DIRECTORY_SEPARATOR.'TaxaSearch.php';
      
      require __DIR__.DIRECTORY_SEPARATOR.'region'.DIRECTORY_SEPARATOR.'Region.php';
      require __DIR__.DIRECTORY_SEPARATOR.'region'.DIRECTORY_SEPARATOR.'RegionColl.php';

      return self::$instance;
   }
}
