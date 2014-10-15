<?php
namespace flora\taxa;
/**
 * Taxa class
 *
 * @author caiofior
 */
class Taxa extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa');
   }
   /**
      * Loads data from its id
      * @param int $id
      */
    public function loadFromId($id) {
         if (
         !is_object($this->table) ||
         !$this->table instanceof \Zend\Db\TableGateway\TableGateway
         ) {
            throw new \Exception('No database table associated with this content',1409011101);
        }
        $select =  $this->table->getSql()->select()
        ->where('`taxa`.`id` = '.intval($id))
        ->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id',array('taxa_kind_initials'=>'initials','taxa_kind_id_name'=>'name'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $data = $this->table->selectWith($select)->current();
        if (is_object($data)) {
            $this->data = $data->getArrayCopy();
            $this->rawData = $this->data;
        }
 
    }
    /**
     * Adds creation datetime
     */
    public function insert() {
       unset($this->data['taxa_kind_initials']);
       unset($this->data['taxa_kind_id_name']);
       $this->data['creation_datetime']=date('Y-m-d H:i:s');
       $this->data['change_datetime']=date('Y-m-d H:i:s');
       parent::insert();
    }
    /**
     * Updates the data
     */
    public function update() {
       unset($this->data['taxa_kind_initials']);
       unset($this->data['taxa_kind_id_name']);
       $this->data['change_datetime']=date('Y-m-d H:i:s');
       parent::update();
    }
    /**
     * Returns the associated collection of regions
     * @return \flora\region\RegionColl
     */
    public function getRegionColl()
    {
       $regionColl = new \flora\region\RegionColl($this->db);
       $regionColl->loadAll();
       if (array_key_exists('id', $this->data)&& $this->data['id'] != '') {
         $resultSet = $this->db->query('SELECT `id_region` FROM `taxa_region` 
                WHERE `id_taxa`='.intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
         foreach ( $resultSet->toArray() as $region) {
            $filteredRegionColl = $regionColl->filterByAttributeValue($region['id_region'],'id');
            $filteredRegion = $filteredRegionColl->getFirst();
            $filteredRegion->setData('1','selected');
         }
       }
       return $regionColl;
    }
    /**
     * Sets teh regions associated with a taxa
     * @param array $regions
     */
    public function setRegions(array $regions) {
       if (array_key_exists('id', $this->data) && $this->data['id'] != '')
       $this->db->query('DELETE FROM `taxa_region` 
              WHERE `id_taxa`='.intval($this->data['id'])
              , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
       foreach ($regions as $region) {
          $this->db->query('INSERT INTO `taxa_region` 
              (id_taxa,id_region)
              VALUES
              ('.intval($this->data['id']).',"'.addslashes($region).'")'
              , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
       }
    }
    /**
     * Loads associated taxa kind
     * @return \flora\taxa\TaxaKind
     */
    public function getTaxaKind()
    {
        $taxaKind = new \flora\taxa\TaxaKind($this->db);
        if (array_key_exists('taxa_kind_id',$this->data) && $this->data['taxa_kind_id'] != '') {
         $taxaKind->loadFromId($this->data['taxa_kind_id']);
        }
        return $taxaKind;
    }
     /**
     * Loads associated dicotomic key
     * @return \flora\dico\Dico
     */
    public function getDico()
    {
        $dico = new \flora\dico\Dico($this->db);
        if (array_key_exists('dico_id',$this->data) && $this->data['dico_id'] != '') {
         $dico->loadFromId($this->data['dico_id']);
        }
        return $dico;
    }
    /**
     * Adds an attribute based on its name and value
     * @param string $name
     * @param string $value
     */
    public function addTaxaAttribute ($name,$value) {
         $attibute = new \flora\taxa\TaxaAttribute($this->db);
         $attibute->loadFromName($name);
         if ($attibute->getData('id')== '') {
            $attibute->setData($name, 'name');
            $attibute->insert();
            $attibute->loadFromName($name);
         } 
         $this->db->query('REPLACE INTO `taxa_attribute_value` 
         (`id_taxa`,`id_taxa_attribute`,`value`)
         VALUES
         ('.intval($this->data['id']).','.intval($attibute->getData('id')).',"'.addslashes($value).'")'
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
    /**
     * Adds an attribute based on its id and value
     * @param int $id
     * @param string $value
     */
    public function addTaxaAttributeById ($id,$value) {
         $attibute = new \flora\taxa\TaxaAttribute($this->db);
         $attibute->loadFromId($id);
         if ($attibute->getData('id')== '') {
            throw new \Exception('The id does not exists '.$id,1410011528);
         } 
         $this->db->query('REPLACE INTO `taxa_attribute_value` 
         (`id_taxa`,`id_taxa_attribute`,`value`)
         VALUES
         ('.intval($this->data['id']).','.intval($id).',"'.addslashes($value).'")'
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
    /**
     * Gets an attribute value based on its id
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getAttributeById ($id) {
         $attibute = new \flora\taxa\TaxaAttribute($this->db);
         $attibute->loadFromId($id);
         if ($attibute->getData('id')== '') {
            throw new \Exception('The id does not exists '.$id,1410011528);
         } 
         $value = $this->db->query('SELECT `value` FROM `taxa_attribute_value` 
         WHERE `id_taxa` = '.intval($this->data['id']).' AND `id_taxa_attribute` ='.intval($id)
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
         $value = $value->value;
         return $value;
    }
     /**
     * Deletes all taxa attributes
     */
    public function deleteAllTaxaAttributes() {
         $this->db->query('DELETE FROM  `taxa_attribute_value` 
         WHERE `id_taxa`='.intval($this->data['id'])
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
    /**
     * Deletes an attribute by id
     * @param int $id
     */
    public function deleteTaxaAttributeById($id) {
         $this->db->query('DELETE FROM  `taxa_attribute_value` 
         WHERE `id_taxa`='.intval($this->data['id']).' AND `id_taxa_attribute` = '.intval($id)
         , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
    /**
     * Geths the associated taxa image collection
     * @return \flora\taxa\TaxaImageColl
     */
    public function getTaxaImgeColl()
    {
       $taxaImageColl = new \flora\taxa\TaxaImageColl($this->db);
       if (array_key_exists('id', $this->data) && $this->data['id'] != ''){
        $taxaImageColl->loadAll(array('taxa_id'=>$this->data['id']));
       }
       return $taxaImageColl;
    }

    /**
     * Return a collection of taxa attributes
     * @return \flora\taxa\TaxaAttributeColl
     */
    public function getTaxaAttributeColl()
    {
       $taxaAttributeColl = new \flora\taxa\TaxaAttributeColl($this->db);
       if (array_key_exists('id', $this->data) && $this->data['id']!= '') {
         $taxaAttributeColl->loadAll(array('taxa_id'=>$this->data['id']));
       }
       return $taxaAttributeColl;
    }
    /**
     * Returns a colleciont of the parents of a taxa
     * @return \flora\taxa\TaxaColl
     */
    public function getParentColl() {
       $parentTaxaColl = new \flora\taxa\TaxaColl($this->db);
       
       $taxa_id = $this->data['id'];
       while (true) {
         $dico_id = $this->db->query('SELECT `id_dico` FROM `dico_item` 
           WHERE `taxa_id` = '.intval($taxa_id)
           , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
         $dico_id = $dico_id->id_dico;

         $taxa_id = $this->db->query('SELECT `id` FROM `taxa` 
           WHERE `dico_id` = '.intval($dico_id)
           , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
         if (!is_object($taxa_id)) {
            break;
         }
         $taxa_id = $taxa_id->id;

         $taxa = new \flora\taxa\Taxa($this->db);
         $taxa->loadFromId($taxa_id);
         $parentTaxaColl->prependItem($taxa);
       }
       
       return $parentTaxaColl;
    }

}