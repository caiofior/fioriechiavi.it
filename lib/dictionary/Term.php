<?php

namespace dictionary;

/**
 * Taxa class
 *
 * @author caiofior
 */
class Term extends \Content {

    /**
     * Associates the database table
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db) {
        parent::__construct($db, 'term');
    }
    
    /**
     * Adds creation datetime
     */
    public function insert() {
	unset($this->data['id']);        
        parent::insert();
    }

    /**
     * Deletes also the associated data
     */
    public function delete() {
        foreach ($this->getTermImageColl()->getItems() as $image) {
            $image->delete();
        }
        parent::delete();        
    }

    /**
     * Geths the associated taxa image collection
     * @return \flora\taxa\TaxaImageColl
     */
    public function getTermImageColl() {
        $termImageColl = new \dictionary\TermImageColl($this->db);
        if (array_key_exists('id', $this->data) && $this->data['id'] != '') {
            $termImageColl->loadAll(array('term_id' => $this->data['id']));
        }
        return $termImageColl;
    }

}
