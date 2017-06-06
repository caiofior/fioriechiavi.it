<?php

namespace flora\taxa;

/**
 * Taxa searchclass
 *
 * @author caiofior
 */
class TaxaSearch extends \Content {
    /**
     * Taxa reference
     * @var \flora\taxa\Taxa $taxa
     */
    private $taxa;
    /**
     * Associates the database table
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db) {
        parent::__construct($db, 'taxa_search');
    }
    /**
     * Loads taxa search from taxa
     * @param \flora\taxa\Taxa $taxa
     */
    public function loadFromTaxa(\flora\taxa\Taxa $taxa) {
        $this->taxa = $taxa;
        parent::loadFromId($taxa->getData('id'));
    }
    /**
     * Updates the search data
     * @return string
     */
    public function update() {
        $message = '';
        if(!array_key_exists('taxa_id', $this->data) || $this->data['taxa_id'] == '') {
            $this->db->query('INSERT INTO `taxa_search` SET `taxa_id` = ' . intval($this->taxa->getData('id'))
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
        
        $this->updateFullText();
        
        $this->db->query('DELETE FROM `taxa_search_attribute` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        $this->updateAltitudeAttribute();
        $message .=$this->updateFloweringAttribute();
        $message .=$this->updateNsm();
        
        return $message;
    }
    /**
     * Updates full text data
     */
    private function updateFullText() {
	    $this->db->query('SET group_concat_max_len=15000', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $this->db->query('UPDATE `taxa_search` SET 
            `text` = CONCAT(
            COALESCE((SELECT `name` FROM `taxa` WHERE `id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT `description` FROM `taxa` WHERE `id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT GROUP_CONCAT(`value` SEPARATOR " ") FROM `taxa_attribute_value` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT GROUP_CONCAT(`text` SEPARATOR " ") FROM `dico_item` WHERE `parent_taxa_id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT GROUP_CONCAT(`name` SEPARATOR " ") FROM `add_dico` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')).'),""),
            " ",
            COALESCE((SELECT GROUP_CONCAT(`text` SEPARATOR " ") FROM `add_dico_item` WHERE `dico_id` IN (SELECT `id` FROM `add_dico` WHERE `taxa_id` = ' . intval($this->taxa->getData('id')).')),"")
            ) WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
    /**
     * Prepares altitude attribute for indexing
     */
    private function updateAltitudeAttribute() {
        if (is_array($this->db->config->attributes->altitude->toArray())) {        
            $altitudeValues = array();
            $altitude = $this->db->query('
                SELECT `value`
                FROM `taxa_attribute_value` WHERE
                `taxa_attribute_id` IN ('.implode(',',$this->db->config->attributes->altitude->toArray()).') AND
                `taxa_id` ='.intval($this->taxa->getData('id'))
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            foreach($altitude->toArray() as $altitudeValue) {
                $altitudeValues[]= $altitudeValue['value'];
            }
            array_unique($altitudeValues);
            if(sizeof($altitudeValues)>1) {
                $step = $this->db->config->attributes->altitudeStep;
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
        }
    }
    /**
     * Prepares flowering attribute for indexing
     * @return string
     */
    private function updateFloweringAttribute() {
        $message = '';
        if (is_array($this->db->config->attributes->flowering->toArray())) { 
            $nameToNumber=$this->db->config->attributes->floweringNames->toArray();
            $floweringValues = array();
            $flowering = $this->db->query('
                SELECT `value`
                FROM `taxa_attribute_value` WHERE
                `taxa_attribute_id` IN ('.implode(',',$this->db->config->attributes->flowering->toArray()).') AND
                `taxa_id` ='.intval($this->taxa->getData('id'))
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            foreach($flowering->toArray() as $floweringValue) {
                if (array_key_exists($floweringValue['value'],$nameToNumber)){
                    $floweringValues[]= $nameToNumber[$floweringValue['value']];    
                } else {
                    $message .= 'Taxa '.$this->taxa->getData('name').' '.$this->taxa->getData('id').' has a wrong flowering attributes'.PHP_EOL;
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
                            `attribute_id`=2,
                            `value`= '.$floweringStep.'
                            ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);        
                }
            }
        }
        return $message;
    }
    /**
     * Updates nested sets module
     * @return string
     */
    private function updateNsm() {
        $message = '';
        $taxaSearchObj=null;
        $updateFtr =true;
        
        $taxaParentResultset = $this->db->query('SELECT `parent_taxa_id` FROM `dico_item` WHERE `taxa_id` ='.intval($this->taxa->getData('id'))
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        while ($taxaParentResultset->valid()) {
            $taxaParentObj = $taxaParentResultset->current();
            if (is_object($taxaParentObj) && is_numeric($taxaParentObj->parent_taxa_id)) {
                $parentTaxaId = intval($taxaParentObj->parent_taxa_id);
                $taxaSearchObj = $this->db->query('SELECT `lft`,`rgt` FROM `taxa_search` WHERE `taxa_id` = '.$parentTaxaId
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();

                if (is_object($taxaSearchObj)) {
                    $taxaParentNsmObj = $this->db->query('SELECT `taxa_id` FROM `taxa_search` WHERE
                        `lft` <='.intval($taxaSearchObj->lft).' AND `rgt` >='.intval($taxaSearchObj->lft).' 
                         ORDER BY `rgt`-`lft` ASC
                         LIMIT 1 '
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->current();
                    if(method_exists($taxaParentNsmObj,'taxa_id')) {
                        $parentTaxaIdNsm =  $taxaParentNsmObj->taxa_id;
                        $updateFtr = $parentTaxaId != $parentTaxaIdNsm;
                    }
                    unset($taxaParentNsmObj);
                    break;
                }
            }
            $taxaParentResultset->next();
        }
        
        $this->db->query('LOCK TABLES `taxa_search` WRITE,`dico_item` WRITE', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
             
        if (is_object($taxaSearchObj)) {
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
                    $message .= 'Taxa '.$this->taxa->getData('name').' '.$this->taxa->getData('id').' has no parent'.PHP_EOL;
                }
            }
            $updateFtr =true;
        }
        
        if ($updateFtr) {
            $this->db->query('UPDATE `taxa_search` SET 
                `lft` = '.intval($parLft+1).',
                `rgt`= '.intval($parLft+2).'
                 WHERE `taxa_id` = ' . intval($this->taxa->getData('id')), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            unset($taxaSearchObj);
        }
        $this->db->query('UNLOCK TABLES', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $message;
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
    /**
     * Generates the sitemap
     */
    public function generateSitemap() {
        $handler = fopen(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'sitemap.xml', 'w');
        fwrite($handler,<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
      <loc>{$this->db->config->baseUrl}</loc>
       <priority>1</priority>
    </url>
    <url>
      <loc>{$this->db->config->baseUrl}search.php</loc>
       <priority>1</priority>
    </url>
    <url>
      <loc>{$this->db->config->baseUrl}map.php</loc>
       <priority>1</priority>
    </url>
    <url>
      <loc>{$this->db->config->baseUrl}observation.php</loc>
       <priority>1</priority>
    </url>
    <url>
      <loc>{$this->db->config->baseUrl}rss.php</loc>
       <priority>1</priority>
    </url>
      
EOT
        );
        $newTaxaObservationColl  = new \floraobservation\TaxaObservationColl($this->db);
        $newTaxaObservationColl->loadAll(array(
            'iDisplayStart'=>0,
            'iDisplayLength'=>100,
            'sColumns'=>'datetime',
            'iSortingCols'=>'1',
            'iSortCol_0'=>'0',
            'sSortDir_0'=>'DESC',
	    'valid'=>true
        ));
        foreach($newTaxaObservationColl->getItems() as $newTaxaObservation) : 
        fwrite($handler,<<<EOT
    <url>
      <loc>{$this->db->config->baseUrl}observation.php?id={$newTaxaObservation->getData('id')}</loc>

EOT
                );
                if ($newTaxaObservation->getData('datetime') != '' ):
                    $data = substr($newTaxaObservation->getData('datetime'),0,10);
                    fwrite($handler,<<<EOT
      <lastmod>{$data}</lastmod>

EOT
                );
                endif;
                fwrite($handler,<<<EOT
    </url>

EOT
                );
        endforeach;
        $newTaxaColl  = new \flora\taxa\TaxaColl($this->db);
        $newTaxaColl->loadAll(array(
            'iDisplayStart'=>0,
            'iDisplayLength'=>200-$newTaxaObservationColl->count(),
            'sColumns'=>'change_datetime',
            'iSortingCols'=>'1',
            'iSortCol_0'=>'0',
            'sSortDir_0'=>'DESC',
            'status'=>true
        ));
        
        foreach($newTaxaColl->getItems() as $newTaxa) : 
                fwrite($handler,<<<EOT
    <url>
      <loc>{$this->db->config->baseUrl}?id={$newTaxa->getData('id')}</loc>

EOT
                );
                if ($newTaxa->getData('change_datetime') != '' ):
                    $data = substr($newTaxa->getData('change_datetime'),0,10);
                    fwrite($handler,<<<EOT
      <lastmod>{$data}</lastmod>

EOT
                );
                endif;
                fwrite($handler,<<<EOT
    </url>

EOT
                );
        endforeach;
        
        fwrite($handler,<<<EOT
</urlset>
EOT
        );
        fclose($handler);
    }
}
