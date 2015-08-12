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
   public function getUrl(array $size=array()) {
      if (array_key_exists('filename', $this->data)) {
         if (array_key_exists('x', $size) && array_key_exists('y', $size)) {
             $url = self::imageBaseDir.$size['x'].'x'.$size['y'];
             $path = $this->db->baseDir.DIRECTORY_SEPARATOR.$url;
             if (!is_dir($path)) {
                 mkdir($path);
             }
             $url .= '/'.$this->data['filename'];
             if (!is_file($path.DIRECTORY_SEPARATOR.$this->data['filename'])) {
                $dirs = array_filter(explode(DIRECTORY_SEPARATOR, $this->data['filename']));
                $fileName = array_pop($dirs);
                foreach ($dirs as $dir) {
                    $path .= DIRECTORY_SEPARATOR.$dir;
                    if (!is_dir($path)) {
                        mkdir($path);
                    }
                }
                $path .= DIRECTORY_SEPARATOR.$fileName;
                $this->generateImageThumbnail($this->getPath(), $path,$size);
             }
             return $url;
         } else {
            return self::imageBaseDir.$this->data['filename'];
         }
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
      return $this->db->baseDir.DIRECTORY_SEPARATOR.self::imageBaseDir;
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
   /**
    * Creates a thumbnail
    * @param string $source_image_path
    * @param string $thumbnail_image_path
    * @param array $size
    * @throws Exception
    */
   private function generateImageThumbnail($source_image_path, $thumbnail_image_path,$size) {
        if (!is_file($source_image_path)) {
            throw new \Exception('Image not found '.$source_image_path,1508051434);
        }
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($source_image_path);
                break;
            default:
                throw new \Exception('Image type not supported '.$source_image_type,1508051433);
                break;
        }
        if ($source_gd_image === false) {
            throw new Exception('Unable to find image type',1508051433);
        }
        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $size['x'] / $size['y'];
        if ($source_image_width <= $size['x'] && $source_image_height <= $size['y']) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($size['y'] * $source_aspect_ratio);
            $thumbnail_image_height = $size['y'];
        } else {
            $thumbnail_image_width = $size['x'];
            $thumbnail_image_height = (int) ($size['x'] / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                imagegif($thumbnail_gd_image, $thumbnail_image_path);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail_gd_image, $thumbnail_image_path, 90);
                break;
            default:
                throw new \Exception('Image type not supported '.$source_image_type,1508051433);
                break;
        }
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);
    }
}