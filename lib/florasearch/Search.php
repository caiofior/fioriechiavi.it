<?php
namespace florasearch;
/**
 * Search if flora taxa
 *
 * @author caiofior
 */
class Search {
    /**
    * Zend DB
    * @var Zend_Db
    */
    private $db;
    /**
     * Request parameters
     * @var array
     */
    private $request=array();
    /**
     * taxa reference
     * @var \flora\taxa\Taxa
     */
    private $content;
    /**
     * Reference to complete region Collection
     * @var \flora\region\RegionColl
     */
    private $regionColl;
    /**
     * Altitude array
     * @var array
     */
    private $altitude=array();
    /**
     *Array of attributes ids
     * @var array
     */
    private $attributeId=array();
    
    
    /**
     * Instantiates the search
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db) {
        $this->db = $db;
        $this->content = new \flora\taxa\TaxaSearch($this->db);
        $this->regionColl = new \flora\region\RegionColl($this->db);
        $this->regionColl->loadAll();
        $this->altitude= array_flip(range(0,2500,500));
        foreach($this->altitude as $altitude=>$value) {
            $this->altitude[$altitude]=array('count'=>0);
        }
        foreach($this->db->query('SELECT `name`,`id` FROM `taxa_attribute`'
        , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->toArray() as $attribute) {
            $this->attributeId[$attribute['name']]=$attribute['id'];
        }
    }
    /**
     * Sets the request/*
     * @param array $request
     */
    public function setRequest (array $request) {
        $this->request = $request;
    }
    /**
     * Count all the items
     * @throws \Exception
     */
    public function getTaxaCountAll() {
        $select=$this->createSelect();
        $select->columns(array('count'=>new \Zend\Db\Sql\Expression('COUNT(`taxa_search`.`taxa_id`)')));
        try{
                $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
                $results = $statement->execute();
                $resultSet = new \Zend\Db\ResultSet\ResultSet();
                $resultSet->initialize($results);
                return $resultSet->current()->count;
        }
        catch (\Exception $e) {
               $mysqli = $this->db->getDriver()->getConnection()->getResource();  
               if (array_key_exists('firephp', $GLOBALS) && !headers_sent())
                   $GLOBALS['firephp']->error('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error);
               throw new \Exception('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error,1401301242);
        }
        
    }
    /**
     * Gets the taxa collection
     * @return \flora\taxa\TaxaColl
     */
    public function getTaxaColl() {
        $data = array();
        $select=$this->createSelect();
        $select->columns(array(
            'taxa_id'
        ));
        $sql = $select->getSqlString($this->db->getPlatform());
        $table = new \Zend\Db\TableGateway\TableGateway('taxa',$this->db);
        $select = $table->getSql()->select();
        $select->join('taxa_kind', 'taxa_kind.id=taxa.taxa_kind_id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
        $select->columns(array(
            'id'=>new \Zend\Db\Sql\Expression('`taxa`.`id`'),
            'name'=>new \Zend\Db\Sql\Expression('`taxa`.`name`'),
            'taxa_kind_initials'=>new \Zend\Db\Sql\Expression('`taxa_kind`.`initials`'),
            'taxa_kind_id_name'=>new \Zend\Db\Sql\Expression('`taxa_kind`.`name`')
        ));
        $select->where('`taxa`.`id` IN ('.$sql.')');
        
        if (
                array_key_exists('start', $this->request) &&
                $this->request['start']!= '' &&
                array_key_exists('pagelength', $this->request) &&
                $this->request['pagelength']!= ''
            ) {
             $select->offset($this->request['start']);
        }
        if (
                array_key_exists('pagelength', $this->request) &&
                $this->request['pagelength']!= ''
            ) {
             $select->limit($this->request['pagelength']);
        }
        try{
                $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
                $results = $statement->execute();
                $resultSet = new \Zend\Db\ResultSet\ResultSet();
                $resultSet->initialize($results);
                $data = $resultSet->toArray(); 
            }
            catch (\Exception $e) {
               $mysqli = $this->db->getDriver()->getConnection()->getResource();  
               if (array_key_exists('firephp', $GLOBALS) && !headers_sent())
                   $GLOBALS['firephp']->error('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error);
               throw new \Exception('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error,1401301242);
            }
        $taxaColl = new \flora\taxa\TaxaColl($this->db);
        foreach($data as $taxaData) {
            $taxa = $taxaColl->addItem();
            $taxa->setData($taxaData);
        }
        return $taxaColl;
    }
     /**
     * Gets a collection of regions
     * @return \flora\region\RegionColl
     * @throws \Exception
     */
    public function getRegionColl() {
        return $this->regionColl;
    }
    /**
     * Gets a collection of filtered regions
     * @return \flora\region\RegionColl
     * @throws \Exception
     */
    public function getFilteredRegionColl(){
        $select=$this->createSelect(array('region'));
        $select->columns(array(
            'taxa_id'
        ));
        $sql = $select->getSqlString($this->db->getPlatform());
        $table = new \Zend\Db\TableGateway\TableGateway('taxa_region',$this->db);
        $select = $table->getSql()->select();
        $select->join('region', 'region.id=taxa_region.region_id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
        $select->columns(array(
            'id'=>new \Zend\Db\Sql\Expression('`taxa_region`.`region_id`'),
            'name'=>new \Zend\Db\Sql\Expression('`region`.`name`'),
            'count'=>new \Zend\Db\Sql\Expression('COUNT(`taxa_region`.`taxa_id`)')
        ));
        $select->where('`taxa_region`.`taxa_id` IN ('.$sql.')');
        $select->group('taxa_region.region_id');
        try{
               $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
               $results = $statement->execute();
               $resultSet = new \Zend\Db\ResultSet\ResultSet();
               $resultSet->initialize($results);
               $data = $resultSet->toArray(); 
           }
           catch (\Exception $e) {
              $mysqli = $this->db->getDriver()->getConnection()->getResource();  
              if (array_key_exists('firephp', $GLOBALS) && !headers_sent())
                  $GLOBALS['firephp']->error('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error);
              throw new \Exception('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error,1401301242);
           }
        foreach($data as $regionData) {
            if ($regionData['name'] == '') continue;
            if (
                    array_key_exists('region', $this->request) && 
                    is_array($this->request['region']) && 
                    in_array($regionData['id'], $this->request['region'])
                ) {
                   $regionData['selected']=true;
                }
            foreach($this->regionColl->getItems() as $region) {
                if ($region->getData('id')==$regionData['id']) {
                    $region->setData($regionData);
                }
            }    
        }
        return $this->regionColl;
    }
    /**
     * Gets altitude array
     * @return array
     */
    public function getAltitudeArray() {
        return array_keys($this->altitude);
    }
    /**
     * Gets filtered altitude array
     * @return array
     */
    public function getFilteredAltitudeArray() {
        $select=$this->createSelect(array('altitude'));
        $select->columns(array(
            'taxa_id'
         ));
        $sql = $select->getSqlString($this->db->getPlatform());
        $table = new \Zend\Db\TableGateway\TableGateway('taxa_search_attribute',$this->db);
        $select = $table->getSql()->select();
        $select->columns(array(
            'count'=>new \Zend\Db\Sql\Expression('COUNT(`taxa_search_attribute`.`taxa_id`)'),
            'altitude'=>'value',
        ));
        $select->where('`attribute_id` = 1');
        $select->where('`taxa_id` IN ('.$sql.')');
        $select->group('value');
        
        try {
               $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
               $results = $statement->execute();
               $resultSet = new \Zend\Db\ResultSet\ResultSet();
               $resultSet->initialize($results);
               foreach($resultSet->toArray() as $data) {
                   $this->altitude[$data['altitude']]=array('count'=>$data['count']);
               } 
        }
           catch (\Exception $e) {
              $mysqli = $this->db->getDriver()->getConnection()->getResource();  
              if (array_key_exists('firephp', $GLOBALS) && !headers_sent())
                  $GLOBALS['firephp']->error('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error);
              throw new \Exception('Error in '. get_called_class().' on query '.$select->getSqlString($this->db->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error,1401301242);
        }
        
        if(array_key_exists('altitude', $this->request) && is_array($this->request['altitude'])) {
             foreach ($this->request['altitude'] as $altitude) {
                 if (array_key_exists($altitude, $this->altitude)) {
                     if (!is_array($this->altitude[$altitude])) {
                         $this->altitude[$altitude]=array();
                     }
                     $this->altitude[$altitude]['selected']=true;
                 }
             }
        }
        return $this->altitude;
    }
    /**
     * Create the select
     * @return \Zend\Db\Sql\Select
     */
    private function createSelect(array $avoid=array()) {
        $select = $this->content->getTable()->getSql()->select();
        if (array_key_exists('text', $this->request) && $this->request['text']!= '') {
            $select->where('
                MATCH (`taxa_search`.`text`) AGAINST ( "'.addslashes($this->request['text']).'" IN NATURAL LANGUAGE MODE)
                ');            
        }
        if  (
                array_key_exists('region', $this->request) && 
                is_array($this->request['region']) && 
                $this->regionColl->count() != sizeof($this->request['region']) &&
                !in_array('region',$avoid)
            ) {
            $this->request['region']=array_map('intval',$this->request['region']);
            $select->join('taxa_region', 'taxa_search.taxa_id=taxa_region.taxa_id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
            $select->where('`taxa_region`.`region_id` IN ("'.implode('","',$this->request['region']).'")');
        }
        if  (
                array_key_exists('altitude', $this->request) && 
                is_array($this->request['altitude']) && 
                sizeof($this->request['altitude']) < sizeof($this->altitude) &&
                !in_array('altitude',$avoid)
            ) {
            $this->request['altitude']=array_map('intval',$this->request['altitude']);
            $select->where('
                  `taxa_search`.`taxa_id` IN (SELECT `taxa_id` FROM `taxa_search_attribute` WHERE `attribute_id`=1 AND `value` IN ('.implode(',',$this->request['altitude']).'))
            ');
        }
        $select->where('
                (               
                    IFNULL(LENGTH(`taxa_search`.`text`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa_search`.`taxa_id`),0)
                ) > 0
             '); 
        return $select;
    }
}
