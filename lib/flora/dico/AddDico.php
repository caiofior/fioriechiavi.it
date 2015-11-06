<?php
namespace flora\dico;
/**
 * Add dico class
 *
 * @author caiofior
 */
class AddDico extends \Content implements \flora\dico\DicoInt
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'add_dico');
   }
   /**
     * Returns a collection of the parents of a taxa
     * @return \flora\taxa\AddDicoItemColl
     */
    public function getDicoItemColl($edit = false) {
        $dicoItemColl = new \flora\dico\AddDicoItemColl($this->db);
        if (array_key_exists('id', $this->data)) {
            $dicoItemColl->loadAll(array('dico_id' => $this->data['id'], 'status' => true));
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
        }
        return $dicoItemColl;
    }
    /**
     * Gets the associated taxa
     * @return \flora\taxa\Taxa
     */
    public function getTaxa() {
        $taxa = new \flora\taxa\Taxa($this->db);
        if (array_key_exists('taxa_id', $this->data) && $this->data['taxa_id']!= '') {
            $taxa->loadFromId($this->data['taxa_id']);
        }
        return $taxa;
    }
    /**
     * Deletes also the associated data
     */
    public function delete() {
        $this->db->query('DELETE FROM `add_dico_item` 
         WHERE `dico_id`=' . intval($this->data['id'])
                , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        parent::delete();
    }
}