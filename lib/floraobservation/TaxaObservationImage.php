<?php
namespace flora\taxa;
/**
 * Taxa Image Class
 *
 * @author caiofior
 */
class TaxaObservationImage extends \Content
{
   /**
    * Base directory
    */
   const imageBaseDir = 'images/taxa_observation';
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa_observation_image');
   }
   /**
    * Deprecated, see moveInsert
    * @throws Exception
    */
   public function insert() {
      throw new \Exception('Deprecated, see moveInsert',1410021642);
   }
   /**
    * Deprecated, see moveInsert
    * @throws Exception
    */
   public function update() {
      throw new \Exception('Deprecated, see moveInsert',1410021642);
   }
   /**
    * Insert the data and saves the image path
    */
   public function moveInsert($inputFile) {
      $this->db->getDriver()->getConnection()->beginTransaction();
      parent::insert();
      try {
         $destinationUrl = $this->moveFile($inputFile);
         $this->setData($destinationUrl,'filename');
         parent::update();
      } catch (\Exception $e) {
         $this->db->getDriver()->getConnection()->rollback();
         if (
                 isset($destinationUrl) &&
                 is_file($this->getBaseFileName().$destinationUrl) &&
                 is_writable(getBaseFileName ().$destinationUrl)
            )
         unlink($this->getBaseFileName().$destinationUrl);
         throw $e;
      }
      $this->db->getDriver()->getConnection()->commit();
   }
   /**
    * Return the image url
    * @return string
    */
   public function getUrl() {
      if (array_key_exists('filename', $this->data)) {
         return self::imageBaseDir.$this->data['filename'];
      }
   }
   /**
    * Return the image path
    * @return string
    */
   public function getPath() {
      return $this->getBaseFileName().$this->data['filename'];
   }
   /**
    * Gets the base part of a file name
    * @return string
    */
   private function getBaseFileName () {
      return $this->db->baseDir.'/'.self::imageBaseDir;
   }
   /**
    * @param string $inputFile input file path
    * @return string save relative url
    * @throws \Exception
    */
   private function moveFile($inputFile) {
      if (!is_file($inputFile)) {
         throw new \Exception('File does not exists '.$inputFile,1410030922);
      }
      if (!is_writable($inputFile)) {
         throw new \Exception('File could not be movend '.$inputFile,1410030923);
      }
      set_error_handler(create_function('', 'throw new \Exception("The file is not an image '.$inputFile.'",1410021647);'),E_WARNING);
      getimagesize($inputFile);
      restore_error_handler();
      $ext = strtolower(pathinfo($inputFile, PATHINFO_EXTENSION));
      if ($ext == '') {
         throw new \Exception('File extension is required',1410030922);
      }
      $destFileName = $this->getBaseFileName();
      set_error_handler(create_function('', 'throw new \Exception("Unable to create directory '.$destFileName.'",1410021647);'),E_WARNING);
      if (!is_dir($destFileName))
         mkdir($destFileName);
      restore_error_handler();
      $destinationUrl = '';
      $fullId = str_pad($this->data['id'], 6, '0', STR_PAD_LEFT);
      for ($c = 0; $c < strlen($fullId)-2; $c+=2 ) {
         $destinationUrl .= '/'.substr($fullId, $c, 2);
         set_error_handler(create_function('', 'throw new \Exception("Unable to create directory '.$destFileName.'",1410021647);'),E_WARNING);
         if (!is_dir($destFileName.$destinationUrl))
            mkdir($destFileName.$destinationUrl);
         restore_error_handler();
      }
      $destinationUrl .= DIRECTORY_SEPARATOR.substr($fullId, -2).'.'.$ext;
      rename($inputFile, $destFileName.$destinationUrl);
      return $destinationUrl;
   }
   /**
    * Removes alto the file
    */
   public function delete() {
      $filePath = $this->getPath();
      if (is_file($filePath) && is_writable($filePath)) {
         unlink($filePath);
      }
      parent::delete();
   }
}