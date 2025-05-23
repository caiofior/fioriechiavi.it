<?php

namespace flora\taxa;

/**
 * Taxa class
 *
 * @author caiofior
 */
class Taxa extends \Content implements \flora\dico\DicoInt {

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
            throw new \Exception('No database table associated with this content', 1409011101);
        }
        $select = $this->table->getSql()->select()
                ->columns(array('*',
                    'status' => new \Zend\Db\Sql\Predicate\Expression('
                (               
                    IFNULL(LENGTH(taxa.description),0)+
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0
               '))
                )
                ->where('`taxa`.`id` = ' . intval($id))
                ->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id', array('taxa_kind_initials' => 'initials', 'taxa_kind_id_name' => 'name','taxa_kind_ord'=>'ord'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $this->data = array();
        $data = $this->table->selectWith($select)->current();
        if (is_object($data)) {
            $this->data = $data->getArrayCopy();
        }
        $this->rawData = $this->data;
    }
    
    /**
     * Loads data from its id
     * @param int $id
     */
    public function loadFromAttributeValue($attributeId, $value) {
        if (
                !is_object($this->table) ||
                !$this->table instanceof \Zend\Db\TableGateway\TableGateway
        ) {
            throw new \Exception('No database table associated with this content', 1409011101);
        }
        $select = $this->table->getSql()->select()
                ->columns(array('*',
                    'status' => new \Zend\Db\Sql\Predicate\Expression('
                (               
                    IFNULL(LENGTH(taxa.description),0)+
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0
               '))
                )
                ->where('`taxa`.`id` = (SELECT `taxa_id` FROM `taxa_attribute_value` WHERE `taxa_attribute_id` = '.intval($attributeId).' AND `value` = "'.  addslashes($value).'" LIMIT 1)')
                ->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id', array('taxa_kind_initials' => 'initials', 'taxa_kind_id_name' => 'name','taxa_kind_ord'=>'ord'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $this->data = array();
        $data = $this->table->selectWith($select)->current();
        if (is_object($data)) {
            $this->data = $data->getArrayCopy();
        }
        $this->rawData = $this->data;
    }

    /**
     * Loads data from its id
     * @param int $id
     */
    public function loadRoot() {
        $this->loadFromId(1);
    }

    /**
     * Adds creation datetime
     */
    public function insert() {
        unset($this->data['taxa_kind_initials']);
        unset($this->data['taxa_kind_id_name']);
        unset($this->data['taxa_kind_ord']);
        unset($this->data['dico_id']);
        unset($this->data['id']);
        if (array_key_exists('eol_id', $this->data) && ($this->data['eol_id']=='' || $this->data['eol_id']==0)) {
            $this->data['eol_id']=null;
        }
        if (array_key_exists('is_list', $this->data) && $this->data['eol_id']=='') {
            $this->data['is_list']=0;
        }
        $this->data['creation_datetime'] = date('Y-m-d H:i:s');
        $this->data['change_datetime'] = date('Y-m-d H:i:s');
        if ($this->db->config->background->useAJAX != true ) {
            pclose(popen('php ' . $this->db->baseDir . '/shell/sitemap.php  > /dev/null &', 'r'));
        }
        parent::insert();
        $this->updateSearch();
        $this->db->query('ALTER TABLE `taxa` ORDER BY `id` DESC'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('ALTER TABLE `taxa_search` ORDER BY `taxa_id` DESC'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $taxaSearch = new \flora\taxa\TaxaSearch($this->db);
        $taxaSearch->generateSitemap();

    }

    /**
     * Updates the data
     */
    public function update() {
        unset($this->data['taxa_kind_initials']);
        unset($this->data['taxa_kind_id_name']);
        unset($this->data['taxa_kind_ord']);
        unset($this->data['status']);
        if (array_key_exists('eol_id', $this->data) && ($this->data['eol_id']=='' || $this->data['eol_id']==0)) {
            $this->data['eol_id']=null;
        }
        if (!array_key_exists('is_list', $this->data)) {
            $this->data['is_list']=0;
        }
        $this->data['change_datetime'] = date('Y-m-d H:i:s');
        if ($this->db->config->background->useAJAX != true ) {
            pclose(popen('php ' . $this->db->baseDir . '/shell/sitemap.php  > /dev/null &', 'r'));
        }
        parent::update();
        $this->updateSearch();
        $this->db->query('ALTER TABLE `taxa` ORDER BY `id` DESC'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('ALTER TABLE `taxa_search` ORDER BY `taxa_id` DESC'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $taxaSearch = new \flora\taxa\TaxaSearch($this->db);
        $taxaSearch->generateSitemap();
    }

    /**
     * Deletes also the associated data
     */
    public function delete() {
        foreach ($this->getTaxaImageColl()->getItems() as $image) {
            $image->delete();
        }
        foreach ($this->getTaxaAttributeColl()->getItems() as $attribute) {
            $attribute->delete();
        }
        foreach ($this->getAddDicoColl()->getItems() as $addDico) {
            $addDico->delete();
        }
        $this->db->query('DELETE FROM `taxa_region` 
         WHERE `taxa_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('UPDATE `dico_item` SET `taxa_id`=NULL 
         WHERE `taxa_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->deleteSearch();
        parent::delete();
        
    }

    /**
     * Returns the associated collection of regions
     * @return \flora\region\RegionColl
     */
    public function getRegionColl() {
        $regionColl = new \flora\region\RegionColl($this->db);
        $regionColl->loadAll();
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $resultSet = $this->db->query('SELECT `region_id` FROM `taxa_region` 
                WHERE `taxa_id`=' . intval($this->data['id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            foreach ($resultSet->toArray() as $region) {
                $filteredRegionColl = $regionColl->filterByAttributeValue($region['region_id'], 'id');
                $filteredRegion = $filteredRegionColl->getFirst();
                $filteredRegion->setData('1', 'selected');
            }
        }
        return $regionColl;
    }

    /**
     * Sets the regions associated with a taxa
     * @param array $regions
     */
    public function setRegions(array $regions) {
        if (array_key_exists('id', $this->data) && $this->data['id'] != '')
            $this->db->query('DELETE FROM `taxa_region` 
              WHERE `taxa_id`=' . intval($this->data['id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        foreach ($regions as $region) {
            $this->db->query('INSERT INTO `taxa_region` 
              (`taxa_id`,`region_id`)
              VALUES
              (' . intval($this->data['id']) . ',"' . addslashes($region) . '")'
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     * Loads associated taxa kind
     * @return \flora\taxa\TaxaKind
     */
    public function getTaxaKind() {
        $taxaKind = new \flora\taxa\TaxaKind($this->db);
        if (array_key_exists('taxa_kind_id', $this->data) && $this->data['taxa_kind_id'] != '') {
            $taxaKind->loadFromId($this->data['taxa_kind_id']);
        }
        return $taxaKind;
    }

    /**
     * Adds an attribute based on its name and value
     * @param string $name
     * @param string $value
     */
    public function addTaxaAttribute($name, $value) {
        $attibute = new \flora\taxa\TaxaAttribute($this->db);
        $name = trim($name);
        $attibute->loadFromName($name);
        if ($attibute->getData('id') == '') {
            $attibute->setData($name, 'name');
            $attibute->insert();
            $attibute->loadFromName($name);
        }
        $this->db->query('REPLACE INTO `taxa_attribute_value` 
         (`taxa_id`,`taxa_attribute_id`,`value`)
         VALUES
         (' . intval($this->data['id']) . ',' . intval($attibute->getData('id')) . ',"' . addslashes($value) . '")'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Adds an attribute based on its id and value
     * @param int $id
     * @param string $value
     */
    public function addTaxaAttributeById($id, $value) {
        $attibute = new \flora\taxa\TaxaAttribute($this->db);
        $attibute->loadFromId($id);
        if ($attibute->getData('id') == '') {
            throw new \Exception('The id does not exists ' . $id, 1410011528);
        }
        $this->db->query('REPLACE INTO `taxa_attribute_value` 
         (`taxa_id`,`taxa_attribute_id`,`value`)
         VALUES
         (' . intval($this->data['id']) . ',' . intval($id) . ',"' . addslashes($value) . '")'
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Gets an attribute value based on its id
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getAttributeById($id) {
        $attibute = new \flora\taxa\TaxaAttribute($this->db);
        $attibute->loadFromId($id);
        if ($attibute->getData('id') == '') {
            throw new \Exception('The id does not exists ' . $id, 1410011528);
        }
        $value = $this->db->query('SELECT `value` FROM `taxa_attribute_value` 
         WHERE `taxa_id` = ' . intval($this->data['id']) . ' AND `taxa_attribute_id` =' . intval($id)
                        , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
        $value = $value->value;
        return $value;
    }

    /**
     * Deletes all taxa attributes
     */
    public function deleteAllTaxaAttributes() {
        $this->db->query('DELETE FROM  `taxa_attribute_value` 
         WHERE `taxa_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Deletes an attribute by id
     * @param int $id
     */
    public function deleteTaxaAttributeById($id) {
        $this->db->query('DELETE FROM  `taxa_attribute_value` 
         WHERE `taxa_id`=' . intval($this->data['id']) . ' AND `taxa_attribute_id` = ' . intval($id)
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Geths the associated taxa image collection
     * @return \flora\taxa\TaxaImageColl
     */
    public function getTaxaImageColl() {
        $taxaImageColl = new \flora\taxa\TaxaImageColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $taxaImageColl->loadAll(array('taxa_id' => $this->data['id']));
        }
        return $taxaImageColl;
    }

    /**
     * Return a collection of taxa attributes
     * @return \flora\taxa\TaxaAttributeColl
     */
    public function getTaxaAttributeColl() {
        $taxaAttributeColl = new \flora\taxa\TaxaAttributeColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $taxaAttributeColl->loadAll(array('taxa_id' => $this->data['id']));
        }
        return $taxaAttributeColl;
    }

    /**
     * Return a collection of taxa attributes
     * @return \flora\taxa\TaxaAttributeColl
     */
    public function getTaxaObservationColl(array $criteria=array()) {
        $taxaObservationColl = new \floraobservation\TaxaObservationColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $criteria['taxa_id'] = $this->data['id'];
            $taxaObservationColl->loadAll($criteria);
        }
        return $taxaObservationColl;
    }
    /**
     * Returns a collection of the parents of a taxa
     * @return \flora\taxa\TaxaColl
     */
    public function getParentColl() {
        $parentTaxaColl = new \flora\taxa\TaxaColl($this->db);

        $taxa_id = $this->data['id'];
        while (true) {
            $taxa_id = $this->db->query('SELECT `parent_taxa_id` FROM `dico_item` WHERE `taxa_id` = ' . intval($taxa_id)
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
            if (is_null($taxa_id)) {
                break;
            }
            $taxa_id = (array) $taxa_id;
            $taxa_id = array_shift($taxa_id);
            if ($taxa_id == 1) {
                break;
            }

            $taxa = new \flora\taxa\Taxa($this->db);
            $taxa->loadFromId($taxa_id);
            $parentTaxaColl->prependItem($taxa);
        }

        return $parentTaxaColl;
    }

    /**
     * Returns a collection of dico items
     * @return \flora\dico\DicoItemColl
     */
    public function getDicoItemColl($edit = false) {
        $dicoItemColl = new \flora\dico\DicoItemColl($this->db);
        if (array_key_exists('id', $this->data)) {
            $dicoItemColl->loadAll(array('parent_taxa_id' => $this->data['id'], 'status' => true));
            if ($edit == true) {
                if ($dicoItemColl->count() == 0) {
                    for ($c = \flora\dico\DicoItem::maxCode; $c >= 0; $c--) {
                        $dicoItem = $dicoItemColl->addItem();
                        $dicoItem->setData($c, 'id');
                        $dicoItem->setData(true, 'incomplete');
                    }
                } else {
                    foreach ($dicoItemColl->getItems() as $dicoItem) {

                        $siblingCode = $dicoItem->getSiblingCode();
                        $siblingDicoItemColl = $dicoItemColl->filterByAttributeValue($siblingCode, 'id');
                        for ($c = (\flora\dico\DicoItem::maxCode - $siblingDicoItemColl->count()-1); $c >= 0 ;$c--) {
                            $dicoItemSibling = $dicoItemColl->addItem();
                            $dicoItemSibling->setData($siblingCode, 'id');
                            $dicoItemSibling->setData(true, 'incomplete');
                        }
                        $possible_taxa = true;
                        $taxaId = $dicoItem->getRawData('taxa_id');
                        if ($taxaId == '' || $taxaId == '0') {
                            $childrenCodeArray = $dicoItem->getChildrenCodeArray();
                            foreach ($childrenCodeArray as $childrenCode) {                            
                                $childrenDicoItemColl = $dicoItemColl->filterByAttributeValue($childrenCode, 'id');
                                if ($childrenDicoItemColl->count() == 0) {
                                    $dicoItemChildren = $dicoItemColl->addItem();
                                    $dicoItemChildren->setData($childrenCode, 'id');
                                    $dicoItemChildren->setData(true, 'incomplete');
                                } else {
                                    $possible_taxa = false;
                                }
                            }
                            if ($possible_taxa === true) {
                                $dicoItem->setData(true, 'possible_taxa');
                            }
                        }
                    }
                }
                $dicoItemColl->sort(array(
                    'field' => 'id'
                ));
            }
        }
        return $dicoItemColl;
    }
    /**
     * Upadates search table data
     * @return message errors
     */
    public function updateSearch() {
       if (
         property_exists($this->db,'cache') &&
         $this->db->cache instanceof \Zend\Cache\Storage\Adapter\AbstractAdapter
        ) {
          $this->db->cache->setItem('map','');
        }
        $taxaSearch = $this->getTaxaSearch();
        return $taxaSearch->update();
    }
    /**
     * Upadates search table data
     */
    public function deleteSearch() {
        $taxaSearch = $this->getTaxaSearch();
        $taxaSearch->delete();
    }
    /**
     * Instantiates the taxa search object
     */
    private function getTaxaSearch() {
        $taxaSearch = new \flora\taxa\TaxaSearch($this->db);
        $taxaSearch->loadFromTaxa($this);
        return $taxaSearch;
    }
    /**
     * Get dico collection of taxa dico with references to this element
     * @return \flora\taxa\TaxaColl
     */
    public function getUsedInDicoColl() {
        $usedInDicoColl = new \flora\taxa\TaxaColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $usedInDicoColl->loadAll(array('doNotCreate'=>1,'used_taxa_id'=>$this->data['id']));
        }
        return $usedInDicoColl;
    }
    /**
     * Get add dico collection of taxa dico with references to this element
     * @return \flora\dico\AddDicoColl
     */
    public function getUsedAddDicoColl() {
        $addDicoColl = new \flora\dico\AddDicoColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $addDicoColl->loadAll(array('doNotCreate'=>1,'used_taxa_id'=>$this->data['id']));
        }
        return $addDicoColl;
    }
    /**
     * Get Additional dico collection
     * @return \flora\dico\AddDicoColl
     */
    public function getAddDicoColl() {
        $addDicoColl = new \flora\dico\AddDicoColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $addDicoColl->loadAll(array('taxa_id'=>$this->data['id']));
        }
        return $addDicoColl;
    }
    /**
     * Adds a dico to a taxa
     * @param array $data
     */
    public function addDico($data) {
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $data['taxa_id']=$this->data['id'];
            $addDico = new \flora\dico\AddDico($this->db);
            $addDico->setData($data);
            $addDico->insert();
        }
    }
    /**
     * Test if taxa can be edited
     * @param \login\user\Profile $filterForTaxaprofile
     * @return bool
     */
    public function profileCanEdit(\login\user\Profile $filterForTaxaprofile) {
	if($filterForTaxaprofile->getEditableTaxaColl()->count() ==0) {
	   return true;
	}
        $sql = 'SELECT COUNT(`id`) FROM `taxa` 
              LEFT JOIN taxa_search ON taxa.id=taxa_search.taxa_id
              WHERE `id` = '. intval($this->data['id']).' AND ( FALSE';
        foreach($filterForTaxaprofile->getEditableTaxaColl()->getItems() as $taxa) {
              $resultSet = $this->db->query('SELECT `lft`,`rgt` FROM `taxa_search` 
              WHERE `taxa_id` = '. intval($taxa->getData('id'))
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
              $resultSet = $resultSet->toArray();
              $taxaSearch = array_shift($resultSet);
              $sql .= ' OR (`lft` >= '.$taxaSearch['lft'].' AND `rgt` <= '.$taxaSearch['rgt'].')';
        }
        $sql .= ')';
        $resultSet = $this->db->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $resultSet = $resultSet->toArray();
        $count = array_shift($resultSet);
        $count = array_shift($count);
        return $count == 1;
    }
     /**
     * Returns a collection of external link providers
     * @return \flora\dico\DicoItemColl
     */
    public function getLinkProviderColl() {
		$linkProviderColl = new \flora\linkprovider\LinkProviderColl($this->db);
        $linkProviderColl->setTaxa($this);
        $linkProviderColl->loadAll();
      return $linkProviderColl;
	}
  public function getGoogleLinkColl() {
    $googleLinkColl = new \flora\linkprovider\GoogleLinkColl($this->db);
    if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
        $criteria['taxa_id'] = $this->data['id'];
        $googleLinkColl->loadAll($criteria);
    }
    return $googleLinkColl;
  }

}
