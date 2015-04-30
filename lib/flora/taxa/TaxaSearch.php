<?php

namespace flora\taxa;

/**
 * Taxa searchclass
 *
 * @author caiofior
 */
class TaxaSearch extends \Content {
    private $taxa;
    /**
     * Associates the database table
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db) {
        parent::__construct($db, 'taxa_search');
    }
    /**
     * Loads taza search from taxa
     * @param \flora\taxa\Taxa $taxa
     */
    public function loadFromTaxa(\flora\taxa\Taxa $taxa) {
        $this->taxa = $taxa;
        parent::loadFromId($taxa->getData('id'));
    }
    /**
     * Updates the search data
     */
    public function update() {
        if(!array_key_exists('taxa_id', $this->data) || $this->data['taxa_id'] == '') {
            $this->db->query('INSERT INTO `taxa_search` SET `taxa_id` = ' . intval($this->taxa->getData('id'))
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
        
        $this->db->query('LOCK TABLES `taxa_search` WRITE,`dico_item` WRITE', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $taxaSearchObj = $this->db->query('SELECT `lft` FROM `taxa_search` WHERE `taxa_id` IN (SELECT `parent_taxa_id` FROM `dico_item` WHERE `taxa_id` ='.intval($this->taxa->getData('id')).')'
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
        if (is_object($taxaSearchObj) && property_exists($taxaSearchObj,'lft')) {
            $parLft = $taxaSearchObj->lft;
            $this->db->query('UPDATE `taxa_search` SET 
            `rgt` = `rgt`+2
            WHERE `rgt` > '.intval($parLft), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);      
            $this->db->query('UPDATE `taxa_search` SET 
            `lft` = `lft`+2
            WHERE `lft` > '.intval($parLft), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);      
            
        } else {
            $taxaSearchObj = $this->db->query('SELECT MAX(`lft`) as lft FROM `taxa_search`'
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
            $parLft = intval($taxaSearchObj->lft);
            if ($parLft > 0) {
                $taxaParentSearchObj = $this->db->query('SELECT `parent_taxa_id` FROM `dico_item` WHERE `taxa_id` ='.intval($this->taxa->getData('id'))
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
                if(!is_object($taxaParentSearchObj)) {
                    $this->db->query('UNLOCK TABLES', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                    $this->db->query('DELETE FROM `taxa_search` WHERE `taxa_id` = ' . intval($this->taxa->getData('id'))
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                    throw new \Exception('Taxa id '.$this->taxa->getData('id').' has no parent', 3004409);
                }
            }
        }
        $this->db->query('UPDATE `taxa_search` SET 
            `lft` = '.intval($parLft+1).',
            `rgt`= '.intval($parLft+2).'
             WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        unset($taxaSearchObj);
        $this->db->query('UNLOCK TABLES', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('UPDATE `taxa_search` SET 
            `text` = CONCAT(
            COALESCE((SELECT `name` FROM `taxa` WHERE `id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT `description` FROM `taxa` WHERE `id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT GROUP_CONCAT(`value` SEPARATOR " ") FROM `taxa_attribute_value` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT GROUP_CONCAT(`text` SEPARATOR " ") FROM `dico_item` WHERE `parent_taxa_id` = ' . intval($this->taxa->getData('id')).'),""),
            " "    
            ) WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        
        $this->db->query('DELETE FROM `taxa_search_attribute` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $altitudeValues = array();
        $altitude = $this->db->query('
            SELECT `value`
            FROM `taxa_attribute_value` WHERE
            `taxa_attribute_id` IN (SELECT `id` FROM `taxa_attribute` WHERE `name` = "Limite altitudinale inferiore" OR  `name` = "Limite altitudinale superiore") AND
            `taxa_id` ='.intval($this->taxa->getData('id'))
        , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        foreach($altitude->toArray() as $altitudeValue) {
            $altitudeValues[]= $altitudeValue['value'];
        }
        array_unique($altitudeValues);
        if(sizeof($altitudeValues)>1) {
            $step = 500;
            $min = floor(min($altitudeValues)/$step)*$step;
            $max = ceil(max($altitudeValues)/$step)*$step;
            
            foreach (range($min,$max,$step) as $altitudeStep) {
                $this->db->query('INSERT IGNORE INTO `taxa_search_attribute` 
                    SET `taxa_id` = ' . intval($this->taxa->getData('id')).',
                        `attribute_id`=1,
                        `value`= '.$altitudeStep.'
                        ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);        
            }
        }
        $nameToNumber=array(
            'Gennaio'=>1,
            'Febbraio'=>2,
            'Marzo'=>3,
            'Aprile'=>4,
            'Maggio'=>5,
            'Giugno'=>6,
            'Luglio'=>7,
            'Agosto'=>8,
            'Settembre'=>9,
            'Ottobre'=>10,
            'Novembre'=>11,
            'Dicembre'=>12
        );
        $floweringValues = array();
        $flowering = $this->db->query('
            SELECT `value`
            FROM `taxa_attribute_value` WHERE
            `taxa_attribute_id` IN (SELECT `id` FROM `taxa_attribute` WHERE `name` = "Inizio fioritura" OR  `name` = "Fine fioritura") AND
            `taxa_id` ='.intval($this->taxa->getData('id'))
        , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        foreach($flowering->toArray() as $floweringValue) {
            if (array_key_exists($floweringValue['value'],$nameToNumber)){
                $floweringValues[]= $nameToNumber[$floweringValue['value']];    
            }
        }
        $floweringValues = array_filter($floweringValues);
        array_unique($floweringValues);
        
        if(sizeof($floweringValues)>1) {
            $min = min($floweringValues);
            $max = max($floweringValues);           
            foreach (range($min,$max,1) as $floweringStep) {
                $this->db->query('INSERT IGNORE INTO `taxa_search_attribute` 
                    SET `taxa_id` = ' . intval($this->taxa->getData('id')).',
                        `attribute_id`=1,
                        `value`= '.$floweringStep.'
                        ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);        
            }
        }

    }
    /**
     * Updates the data
     */
    public function delete() {
        $taxaSearchObj = $this->db->query('SELECT `lft`,`rgt` FROM `taxa_search` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $lft = $taxaSearchObj->lft;
        $rgt = $taxaSearchObj->rgt;
        $wdt = $rgt-$lft;        
        $this->db->query('UPDATE `taxa_search` SET 
        `rgt` = `rgt`-'.$wdt.'
        WHERE `rgt` > '.intval($rgt), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);      
        $this->db->query('UPDATE `taxa_search` SET 
        `lft` = `lft`-'.$wdt.'
        WHERE `lft` > '.intval($rgt), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE); 
        $this->db->query('DELETE FROM `taxa_search` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('DELETE FROM `taxa_search_attribute` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    } 
}
