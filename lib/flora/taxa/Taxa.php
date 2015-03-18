<?php

namespace flora\taxa;

/**
 * Taxa class
 *
 * @author caiofior
 */
class Taxa extends \Content {

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
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`id_taxa`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`id_taxa`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0
               '))
                )
                ->where('`taxa`.`id` = ' . intval($id))
                ->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id', array('taxa_kind_initials' => 'initials', 'taxa_kind_id_name' => 'name'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $data = $this->table->selectWith($select)->current();
        if (is_object($data)) {
            $this->data = $data->getArrayCopy();
            $this->rawData = $this->data;
        }
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
        unset($this->data['dico_id']);
        $this->data['creation_datetime'] = date('Y-m-d H:i:s');
        $this->data['change_datetime'] = date('Y-m-d H:i:s');
        pclose(popen('php ' . $this->db->baseDir . '/shell/sitemap.php  > /dev/null &', 'r'));
        parent::insert();
    }

    /**
     * Updates the data
     */
    public function update() {
        unset($this->data['taxa_kind_initials']);
        unset($this->data['taxa_kind_id_name']);
        unset($this->data['status']);
        $this->data['change_datetime'] = date('Y-m-d H:i:s');
        pclose(popen('php ' . $this->db->baseDir . '/shell/sitemap.php  > /dev/null &', 'r'));
        parent::update();
    }

    /**
     * Delets also the associated data
     */
    public function delete() {
        foreach ($this->getTaxaImgeColl() as $image) {
            $image->delete();
        }
        foreach ($this->getTaxaAttributeColl() as $attribute) {
            $attribute->delete();
        }
        $this->db->query('DELETE FROM `taxa_region` 
         WHERE `id_taxa`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('UPDATE `dico_item` SET `taxa_id`=NULL 
         WHERE `taxa_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
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
            $resultSet = $this->db->query('SELECT `id_region` FROM `taxa_region` 
                WHERE `id_taxa`=' . intval($this->data['id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            foreach ($resultSet->toArray() as $region) {
                $filteredRegionColl = $regionColl->filterByAttributeValue($region['id_region'], 'id');
                $filteredRegion = $filteredRegionColl->getFirst();
                $filteredRegion->setData('1', 'selected');
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
              WHERE `id_taxa`=' . intval($this->data['id'])
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        foreach ($regions as $region) {
            $this->db->query('INSERT INTO `taxa_region` 
              (id_taxa,id_region)
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
        $attibute->loadFromName($name);
        if ($attibute->getData('id') == '') {
            $attibute->setData($name, 'name');
            $attibute->insert();
            $attibute->loadFromName($name);
        }
        $this->db->query('REPLACE INTO `taxa_attribute_value` 
         (`id_taxa`,`id_taxa_attribute`,`value`)
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
         (`id_taxa`,`id_taxa_attribute`,`value`)
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
         WHERE `id_taxa` = ' . intval($this->data['id']) . ' AND `id_taxa_attribute` =' . intval($id)
                        , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
        $value = $value->value;
        return $value;
    }

    /**
     * Deletes all taxa attributes
     */
    public function deleteAllTaxaAttributes() {
        $this->db->query('DELETE FROM  `taxa_attribute_value` 
         WHERE `id_taxa`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Deletes an attribute by id
     * @param int $id
     */
    public function deleteTaxaAttributeById($id) {
        $this->db->query('DELETE FROM  `taxa_attribute_value` 
         WHERE `id_taxa`=' . intval($this->data['id']) . ' AND `id_taxa_attribute` = ' . intval($id)
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Geths the associated taxa image collection
     * @return \flora\taxa\TaxaImageColl
     */
    public function getTaxaImgeColl() {
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
     * Returns a collection of the parents of a taxa
     * @return \flora\taxa\TaxaColl
     */
    public function getParentColl() {
        $parentTaxaColl = new \flora\taxa\TaxaColl($this->db);

        $taxa_id = $this->data['id'];
        while (true) {
            $taxa_id = $this->db->query('SELECT `parent_taxa_id` FROM `dico_item` 
           WHERE `taxa_id` = ' . intval($taxa_id)
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
     * Returns a collection of the parents of a taxa
     * @return \flora\taxa\TaxaColl
     */
    public function getDicoItemColl($edit = false) {
        $dicoItemColl = new \flora\dico\DicoItemColl($this->db);
        $dicoItemColl->loadAll(array('parent_taxa_id' => $this->data['id'], 'status' => true));
        if ($edit == true) {
            if ($dicoItemColl->count() == 0) {
                for ($c = 0; $c < 2; $c++) {
                    $dicoItem = $dicoItemColl->addItem();
                    $dicoItem->setData($c, 'id');
                    $dicoItem->setData(true, 'incomplete');
                }
            } else {
                foreach ($dicoItemColl->getItems() as $dicoItem) {
                    
                    $siblingCode = $dicoItem->getSiblingCode();
                    $siblingDicoItemColl = $dicoItemColl->filterByAttributeValue($siblingCode, 'id');
                    for ($c = 0; $c < (\flora\dico\DicoItem::maxCode - $siblingDicoItemColl->count()); $c++) {
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
        return $dicoItemColl;
    }

}
