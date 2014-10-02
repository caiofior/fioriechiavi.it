<?php
namespace flora\taxa;
/**
 * Taxa Image Class
 *
 * @author caiofior
 */
class TaxaImage extends \Content
{
   const imageBaseDir = '/images/taxa';
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa_image');
   }
   public function insert() {
      throw new Exception('Deprecated, see moveInsert',1410021642);
   }
   /**
    * Insert the data and saves the image path
    */
   public function moveInsert($inputFile) {
      $this->db->getDriver()->getConnection()->beginTransaction();
      parent::insert();
      $destinationFilePath = $this->createFilePath($inputFile);
      $this->setData($this->getFileName(),'filename');
      $this->update();
      $this->db->getDriver()->getConnection()->rollback();
   }
   private function createFilePath($inputFile) {
      $destFileName = $this->db->baseDir.self::imageBaseDir;
      set_error_handler(create_function('', 'throw new Exception("Unable to create directory '.$destFileName.'",1410021647);'),E_WARNING);
      if (!is_dir($destFileName))
         mkdir($destFileName);
      restore_error_handler();
      $fullId = str_pad($this->data['id'], 6, '0', STR_PAD_LEFT);
      for ($c = 0; $c < strlen($fullId)-2; $c+=2 ) {
         $destFileName .= DIRECTORY_SEPARATOR.substr($fullId, $c, 2);
         set_error_handler(create_function('', 'throw new Exception("Unable to create directory '.$destFileName.'",1410021647);'),E_WARNING);
         if (!is_dir($destFileName))
            mkdir($destFileName);
         restore_error_handler();
      }
      $ext = pathinfo($inputFile, PATHINFO_EXTENSION);
      $destFileName .= DIRECTORY_SEPARATOR.substr($fullId, -2).'.'.$ext;
      return $destFileName;
   }
}