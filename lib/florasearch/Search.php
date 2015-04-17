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
     * Instantiates the search
     * @param \Zend\Db\Adapter\Adapter $db
     * @param array $request
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db, array $request) {
        $this->db = $db;
        $this->request = $request;
        $this->content = new \flora\taxa\Taxa($this->db);
    }
    /**
     * Count all the items
     * @throws \Exception
     */
    public function getTaxaCountAll() {
        $select=$this->createSelect();
        $select->columns(array('count'=>new \Zend\Db\Sql\Expression('COUNT(taxa.id)')));
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
     * Create the select
     * @return \Zend\Db\Sql\Select
     */
    private function createSelect() {
        $select = $this->content->getTable()->getSql()->select();
        $select->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id',array('taxa_kind_initials'=>'initials','taxa_kind_id_name'=>'name'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $select->join('dico_item', 'taxa.id=dico_item.taxa_id',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
        $select->join('taxa_attribute_value', 'taxa.id=taxa_attribute_value.id_taxa',array(), \Zend\Db\Sql\Select::JOIN_LEFT);
        if (array_key_exists('text', $this->request) && $this->request['text']!= '') {
            $select->where('
                MATCH (`taxa`.`name`,`taxa`.`description`) AGAINST ( "'.addslashes($this->request['text']).'" IN NATURAL LANGUAGE MODE) OR
                MATCH (`dico_item`.`text`) AGAINST ( "'.addslashes($this->request['text']).'" IN NATURAL LANGUAGE MODE) OR
                MATCH (`taxa_attribute_value`.`value`) AGAINST ( "'.addslashes($this->request['text']).'" IN NATURAL LANGUAGE MODE)
                ');            
        }
        return $select;
    }
}
