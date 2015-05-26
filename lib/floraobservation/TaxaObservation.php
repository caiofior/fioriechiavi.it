<?php
namespace floraobservation;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Taka Observation class
 *
 * @author caiofior
 */
class TaxaObservation extends \Content
{
    /**
     * Conversion coordinate to point
     */
    const C2C=100000;

    /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa_observation');
   }
   /**
    * Load data from id
    * @param int $id
    */
   public function loadFromId($id) {
       parent::loadFromId($id);
       $this->getCoordinates();
   }
   /**
    * Inserts the data and add the coordinates
    */
   public function insert() {
       $this->data['datetime']=date('Y-m-d H:i:s');
       $this->updateCoordinates();
       parent::insert();
   }
   /**
    * Updates data and add the coordinates
    */
   public function update() {
       $this->updateCoordinates();
       parent::update();
   }
   /**
    * Update the coordinates
    */
   private function updateCoordinates() {
       $this->data['position']=new \Zend\Db\Sql\Expression('PointFromText("POINT('.intval($this->rawData['latitude']*self::C2C).' '.intval($this->rawData['longitude']*self::C2C).')")');
   }
   /**
    * Gets coordinates from point data
    */
   private function getCoordinates() {
       $point = $this->db->query('SELECT AsText("'.$this->data['position'].'")' , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
       $point = $point->current()->getArrayCopy()['AsText("'];
       preg_match_all('/[[:digit:]]*/', $point, $matches);
       $matches = array_filter($matches[0]);      
       $this->rawData['latitude']=current($matches)/self::C2C;
       $this->rawData['longitude']=next($matches)/self::C2C;
   }
}