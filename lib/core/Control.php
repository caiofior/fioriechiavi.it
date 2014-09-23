<?php
/**
 * Description of Controler
 *
 * @author caiofior
 */
class Control {
   /**
    * Template path
    * @var string
    */
   private $template =null;
   /**
    * Base dir path
    * @var string
    */
   private $baseDir=null;
   /**
    *Page
    * @var string
    */
   private $page = null;
   /**
    *Page name
    * @var string 
    */
   private $pageName = null;
   /**
    * Validation messages
    * @var array
    */
   private $validationMessages = array('validMessage'=>true);
   /**
    * Sets the template
    * @param string $template
    */
   public function __construct($template) {
      $this->template = $template;
      if (
        (
            !array_key_exists('user', $GLOBALS) ||
            is_null($GLOBALS['user']) ||
            $GLOBALS['user'] instanceof \abbrevia\user\User 
        ) && !array_key_exists('login', $_GET)
   )
   $this->template->setBlock('middle','general/middle.phtml');
else
   $this->template->setBlock('middle','login/middle.phtml');
   }
   /**
    * Sets the base dir
    * @param string $baseDir
    */
   public function setBaseDir($baseDir){
      $this->baseDir = $baseDir;
   }
   /**
    * Gets the base dir
    * @return string
    */
   public function getBaseDir(){
      return $this->baseDir;
   }
   /**
    * Sets the page name
    * @param string $page
    */
   public function setPage($page) {
      $this->page = basename($page);
      if (is_file($this->baseDir.DIRECTORY_SEPARATOR.$this->page))
         require($this->baseDir.DIRECTORY_SEPARATOR.$this->page);
    
   }
   /**
    * Gets the page name
    * @return string
    */
   public function getPage () {
      return $this->page;
   }
   /**
    * Set page name
    * @param string $page
    */
   public function setPageName($page) {
      $this->pageName = ($page);    
   }
   /**
    * Gets the page name
    * @return string
    */
   public function getPageName () {
      return $this->pageName;
   }
   /**
    * Gets template name
    * @return string
    */
   public function getTemplate () {
      return $this->template;
   }
   /**
    * Add value to validation message
    * @param string $key
    * @param string $message
    */
   public function addValidationMessage($key,$message) {
      unset($this->validationMessages['validMessage']);
      $this->validationMessages[$key]=$message;
   }
   /**
    * Return validation message
    * @return string
    */
   public function getValidationMessages() {
      return $this->validationMessages;
   }
   /**
    * If form valid or not
    * @return boolean
    */
   public function formIsValid() {
      if (
              sizeof($this->validationMessages) == 1 &&
              array_array_key_exists('validMessage',$this->validationMessages)
         ) return true;
      return false;
   }
   /**
    * Composes url query part
    * @param array $parameters
    * @return string
    */
   public function getUrlParameters(array $parameters = null) {
      $get = $_GET;
      if (is_array($parameters))
         $get = array_merge ($get,$parameters);
      return '?'.http_build_query($get);
   }
}
