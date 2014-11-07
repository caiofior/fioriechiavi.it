<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Template
 *
 * @author caiofior
 */
class Template {
   /**
    * Base dir
    * @var string
    */
   private $baseDir;
   /**
    * Template file name
    * @var string
    */
   private $templateFileName;
   /**
    * Blocks
    * @var array
    */
   private $blocks = array();
   /**
    * Control reference
    * @var \Control
    */
   private $control;
   /**
    * Object reference
    * @var Object
    */
   private $object;
   /**
    * Caches the file
    * @var bool 
    */
   private $cache=false;
   /**
    * Instantaite the template
    * @param string $baseDir
    * @param string $templateFileName
    */
   public function __construct($baseDir,$templateFileName) {
      $this->baseDir=$baseDir;
      $this->templateFileName = $templateFileName;
   }
   /**
    * Sets the template file name
    * @param string $templateFileName
    */
   public function setTemplate($templateFileName) {
      $this->templateFileName = $templateFileName;
   }
   /**
    * Sets the page name
    * @param string $templateFileName
    */
   public function setTemplatePage($templateFileName) {
      $this->templateFileName = preg_replace('/\/.*/', '/'.$templateFileName, $this->templateFileName);
   }
   /**
    * Sets the block content
    * @param string $blockName
    * @param string $blockFile
    */
   public function setBlock($blockName, $blockFile) {
      $this->blocks[$blockName]=$blockFile;
   }
   /**
    * Renders a block
    * @param string $blockName
    * @throws Exception
    */
   public function renderBlock($blockName) {
      if (!array_key_exists($blockName, $this->blocks))
         throw new Exception('Block undefined '.$blockName, 1401201255);
      $file = $this->baseDir.'view'.DIRECTORY_SEPARATOR.$this->blocks[$blockName];
      if (!is_file($file))
         throw new Exception('Unable to find block '.$file, 1401201254);
      require($this->baseDir.'view'.DIRECTORY_SEPARATOR.$this->blocks[$blockName]);
   }
   /**
    * Renders the page
    * @throws Exception
    */
   public function render() {
      if ( array_key_exists('xhrValidate', $_POST)) {
         header('Content-Type: application/json');
         echo json_encode($this->control->getValidationMessages());
      }
      else if (
              array_key_exists('xhrUpdate', $_POST) &&
              array_key_exists('update', $_POST) &&
              array_key_exists('content', $_POST)
         ) {
         $update = explode(',',$_POST['update']);
         $content = explode(',',$_POST['content']);
         if (sizeof($update) != sizeof($content))
            throw new Exception('update e content array are different',1401301023);
         $response = array();
         foreach($content as $key=>$file) {
            $file = $this->baseDir.'view'.DIRECTORY_SEPARATOR.str_replace('-', DIRECTORY_SEPARATOR, $file).'.phtml';
            if (is_file($file)) {
               ob_start();
               require $file;
               $response[$update[$key]]=ob_get_clean();
            }
         }
         header('Content-Type: application/json');
         echo json_encode($response);
      }
      else {
         $fileName = $this->baseDir.'template'.DIRECTORY_SEPARATOR.$this->templateFileName;
         if (!is_file($fileName))
            throw new \Exception('Template file is missing '.$fileName);
         require($fileName);
         if ($this->cache === true) {
            $GLOBALS['db']->cache->setItem($this->createCacheKey(),  ob_get_flush());
         }
      }
   }
   /**
    * Creates new control
    * @return Control
    */
   public function createControl () {
      $this->control = new Control($this);
      return $this->control;
   }
   /**
    * Gets Control Object
    * @return Control
    */
   public function getControl () {
      return $this->control;
   }
   /**
    * Gets base dir
    * @return string
    */
   public function getBaseDir() {
      return $this->baseDir;
   }
   /**
    * Sets object reference
    * @param Object $object
    */
   public function setObjectData($object) {
      $this->object = $object;
   }
   /**
    * Encodes id for url
    * @param string $id
    * @return string
    */
   public static function encodeId ($id) {
      return mcrypt_encrypt($GLOBALS['config']->crypt->cipher,$GLOBALS['user']->getData('login'),$id,$GLOBALS['config']->crypt->mode ,$GLOBALS['config']->crypt->iv);
   } 
   /**
    * Decodes id for url
    * @param string $code
    * @return string
    */
   public static function decodeId ($code) {
      return trim(mcrypt_decrypt($GLOBALS['config']->crypt->cipher,$GLOBALS['user']->getData('login'),$code,$GLOBALS['config']->crypt->mode,$GLOBALS['config']->crypt->iv ));
   }
   /**
    * Add modify timestamp to file url
    * @param string $relativePath
    * @return string
    */
   public function getUrlModifyTimestamp ($relativePath) {
      if(is_file($this->baseDir.$relativePath)) {
         $relativePath .= '?t='.filemtime($this->baseDir.$relativePath);
      }
      return $relativePath;
   }
   /**
    * Saves in cache all page content
    */
   public function cache() {
      if (
            !array_key_exists('db',$GLOBALS) ||
            !property_exists($GLOBALS['db'],'cache') ||
            !$GLOBALS['db']->cache instanceof Zend\Cache\Storage\Adapter\AbstractAdapter ||
            sizeof($_POST) > 0
         ) {
         return;
      }
      $cacheData = $GLOBALS['db']->cache->getItem($this->createCacheKey());
      if ($cacheData != '') {
         echo $cacheData;
         exit;
      } else {
         ob_start();
         $this->cache = true;
      }
   }
   /**
    * Generates the cache key
    * @return type
    */
   private function createCacheKey () {
      return preg_replace('/[^a-zA-Z0-9_\+\-]/','','url_cache'.$_SERVER['REQUEST_URI']);
   }
}
