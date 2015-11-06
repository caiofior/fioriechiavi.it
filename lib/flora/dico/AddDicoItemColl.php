<?php

namespace flora\dico;

/**
 * Description of Dicotomic key Coll
 *
 * @author caiofior
 */
class AddDicoItemColl extends \ContentColl implements \flora\dico\DicoItemIntColl {

    /**
     * Associates the object
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function __construct($db) {
        parent::__construct(new \flora\dico\AddDicoItem($db));
    }

    /**
     * Customizes select statement
     * @param Zend_Db_Select $select Zend Db Select
     * @param array $criteria Filtering criteria
     * @return Zend_Db_Select Select is expected
     */
    protected function customSelect(\Zend\Db\Sql\Select $select, array $criteria) {
        $select->join('taxa', 'add_dico_item.taxa_id=taxa.id', array('name'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $select->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id', array('initials'), \Zend\Db\Sql\Select::JOIN_LEFT);
        if (
                array_key_exists('dico_id', $criteria) &&
                $criteria['dico_id'] != '') {
            $this->content->setData(intval($criteria['dico_id']), 'dico_id');
            $select->where('dico_id = ' . intval($criteria['dico_id']));
        }
        if (
                array_key_exists('status', $criteria) &&
                $criteria['status'] == true) {
            $select->columns(array(
                '*',
                'status' => new \Zend\Db\Sql\Predicate\Expression('
                (               
                    IFNULL(LENGTH(taxa.description),0)+
                    IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
                    IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
                ) > 0
               ')
            ));
        }
        $select->order('id asc');
        return $select;
    }
    /**
     * Filter collection by attribute and value
     * @param mixed $value
     * @param mixed $field
     * @return \flora\dico\DicoItemColl
     */
    public function filterByAttributeValue($value, $field) {
        $filteredColl = clone $this;
        $filteredColl->emptyColl();
        foreach ($this->items as $item) {
            if ($item->getData($field) === $value)
                $filteredColl->appendItem($item);
        }
        return $filteredColl;
    }
  /**
     * Imports Dico Item from data stream
     *
     * @param type $stream
     * @throws \Exception
     */
    public function import($format, $stream) {
        if (gettype($stream) != 'resource') {
            throw new \Exception('Stream resource must be provided', 1410081107);
        }
        if (!interface_exists('flora\dico\import\Import')) {
            require __DIR__ . '/import/Import.php';
        }
        switch ($format) {
            case 'internal':
                if (!class_exists('flora\dico\import\Internal')) {
                    require __DIR__ . '/import/Internal.php';
                }
                $importClass = new \flora\dico\import\Internal();
                break;
            case 'pignatti':
                if (!class_exists('flora\dico\import\Pignatti')) {
                    require __DIR__ . '/import/Pignatti.php';
                }
                $importClass = new \flora\dico\import\Pignatti();
                break;
            default :
                throw new \Exception('No input format is provided', 1410081107);
                break;
        }
        $dicoItemColl = $importClass->import($this, $stream);
        return $dicoItemColl;
    }

    /**
     * Imports Dico Item from data stream and saves it
     *
     * @param type $stream
     * @throws \Exception
     */
    public function importAndSave($format, $stream) {
        $this->emptyDicoItems();
        $dicoItemColl = $this->import($format, $stream);
        foreach ($dicoItemColl->getItems() as $dicoItem) {
            $dicoItem->replace();
        }
    }
    /**
     * Deletes all dico item associated
     */
    public function emptyDicoItems() {
        if (array_key_exists('dico_id', $this->content->getRawData())) {
            $this->content->getDb()->query('DELETE FROM `add_dico_item`
WHERE `dico_id` = ' . addslashes($this->content->getRawData('dico_id'))
                    , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
    }

}
