<?php
namespace flora\linkprovider;
/**
 * Collection of link providers
 *
 * @author caiofior
 */
class LinkProviderColl extends \ContentColl {
      /**
       * Taxa object
       * @var \flora\taxa\Taxa
       */
      protected $taxa;
      /**
       * Relation with the content
       * @param type $db
       */
      public function __construct($db) {
         parent::__construct(new \flora\linkprovider\LinkProvider($db));
      }
      /**
      * Customizes select statement
      * @param Zend_Db_Select $select Zend Db Select
      * @param array $criteria Filtering criteria
      * @return Zend_Db_Select Select is expected
      */
    protected function customSelect( \Zend\Db\Sql\Select $select,array $criteria ) {
      $select = $this->setFilter($select,$criteria);
      return $select;
    }
     /**
     * Count items
     * @return int
     */
    public function countAll($criteria = array()) {
      $select = $this->content->getTable()->getSql()->select()->columns(array(new \Zend\Db\Sql\Expression('COUNT(*)')));
      $select = $this->setFilter($select,$criteria);
      $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
      $results = $statement->execute();
      $resultSet = new \Zend\Db\ResultSet\ResultSet();
      $resultSet->initialize($results);
      $data = $resultSet->current()->getArrayCopy();
      return intval(array_pop($data));
    }
    /**
     * Sets the filter
     * @param \Zend\Db\Sql\Select $select
     * @param array $criteria
     * @return \Zend\Db\Sql\Select
     */
    private function setFilter ($select,$criteria) {
	   if ($this->taxa instanceof \flora\taxa\Taxa) {
		   if ($this->taxa->getData('id') != '') {
				$select->columns(array(
				'*',
				'taxa_id'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT `taxa_id` FROM `link_taxa` WHERE `provider_id`=`link_provider`.`id` AND `taxa_id`='.intval($this->taxa->getData('id')).' LIMIT 1)'),
				'link'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT `link` FROM `link_taxa` WHERE `provider_id`=`link_provider`.`id` AND `taxa_id`='.intval($this->taxa->getData('id')).' LIMIT 1)'),
				'datetime'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT `datetime` FROM `link_taxa` WHERE `provider_id`=`link_provider`.`id` AND `taxa_id`='.intval($this->taxa->getData('id')).' LIMIT 1)'),
	            'fixed'=>new \Zend\Db\Sql\Predicate\Expression('(SELECT `fixed` FROM `link_taxa` WHERE `provider_id`=`link_provider`.`id` AND `taxa_id`='.intval($this->taxa->getData('id')).' LIMIT 1)')     
				));
		   }         
       }
      return $select;
    }
    /**
     * Sets taxa reference
     * @param \flora\taxa\Taxa $taxa
     */
    public function setTaxa(\flora\taxa\Taxa $taxa) {
       $this->taxa = $taxa;
    }
    /**
     * Gets the firt data form external site
     */
    public function retriveFirst() {
		foreach($this->getItems() as $linkProvider) {
			$dateTime = date_create($linkProvider->getRawData('datetime'));
			if( 
               $linkProvider->getRawData('fixed') != 1 &&
					$linkProvider->getRawData('link') == '' && 
					(
						$linkProvider->getRawData('datetime') == '' ||
						$dateTime->diff(date_create())->format('%d') > 180
					)
					) {
						$linkProvider->retriveData($this->taxa);
			}
		}
	}
}
