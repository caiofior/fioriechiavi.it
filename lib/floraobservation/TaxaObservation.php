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
     * GeoPHP Point reference
     * @var /Point
     */
    private $point;
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
       if (array_key_exists('latitude', $this->rawData) && array_key_exists('longitude', $this->rawData)) {
        $this->data['position']=new \Zend\Db\Sql\Expression('PointFromText("POINT('.floatval($this->rawData['latitude']).' '.floatval($this->rawData['longitude']).')")');
       }
   }
   /**
    * Gets coordinates from point data
    */
   private function getCoordinates() {
       $this->rawData['latitude']=null;
       $this->rawData['longitude']=null;
       if (array_key_exists('position', $this->data) && $this->data['position'] != '') {
        $point = $this->db->query('SELECT AsText("'.$this->data['position'].'")' , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $point = $point->current()->getArrayCopy()['AsText("'];
        preg_match_all('/[[:digit:]\.]*/', $point, $matches);
        $matches = array_filter($matches[0]);      
        $this->rawData['latitude']=current($matches);
        $this->rawData['longitude']=next($matches);
        $this->point = new \Point($this->rawData['latitude'], $this->rawData['longitude']);
       }
   }
    /**
     * Geths the associated taxa onservation image collection
     * @return \floraobservation\TaxaObservationImageColl
     */
    public function getTaxaObservationImageColl() {
        $taxaObservationImageColl = new \flora\taxa\TaxaObservationImageColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $taxaObservationImageColl->loadAll(array('taxa_observation_id' => $this->data['id']));
        }
        return $taxaObservationImageColl;
    }
     /**
     * Deletes also the associated data
     */
    public function delete() {
        foreach ($this->getTaxaObservationImageColl()->getItems() as $image) {
            $image->delete();
        }
        parent::delete();
    }
    /**
    * Sets the data
    * @param variant $data
    * @param string|null $field
    */
    public function setData($data,$field=null){
        parent::setData($data, $field);
        $this->getCoordinates();
    }
    /**
     * Returns the GEOphp point object
     * @return /Point
     */
    public function getPoint () {
        return $this->point;
    }
}