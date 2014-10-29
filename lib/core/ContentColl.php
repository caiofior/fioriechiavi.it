<?php
/**
* Content object collection
*
* Content object collection
*
* @author Claudio Fior <caiofior@gmail.com>
*/
/**
* Content object collection
*
* Content object collection
*
* @author Claudio Fior <caiofior@gmail.com>
*/
abstract class ContentColl {
    /**
* Contet of the collection
* @var Content
*/
    protected $content;
    /**
   * Array of items
   * @var array
   */
    protected $items=array();
    /**
   * Columns array
   * @var array
   */
    protected $columns=null;
    /**
     *Sort criteria
     * @var array
     */
    protected static $sortCriteria=array();
     /**
   * Instantiates the collection
   * @param \Content $content Content base of the collection
   */
    protected function __construct(Content $content) {
        $this->content = $content;
    }
    /**
   * Customizes select statement
   * @param Zend_Db_Select $select Zend Db Select
   * @param array $criteria Filtering criteria
   * @return Zend_Db_Select Select is expected
   */
    abstract protected function customSelect ( \Zend\Db\Sql\Select $select,array $criteria );
    /**
     * Sort criteria
     * @param \Content $a
     * @param \Content $b
     * @return int
     */
    protected static function customSort ($a, $b ){
      $a = $a->getData($a->getPrimaryKey());
      $b = $b->getData($b->getPrimaryKey());
      if ($a == $b) {
         return 0;
      }
      return ($a < $b) ? -1 : 1;
    }
    /**
      * Load all contents
      * @param array $criteria Filtering criteria
      */
    public function loadAll(array $criteria=null) {
        if (is_null($criteria))
            $criteria = array();
        $select = $this->customSelect($this->content->getTable()->getSql()->select(),$criteria);
        if (array_key_exists('sColumns', $criteria) && $criteria['sColumns'] != '') {
           $this->columns= explode(',', $criteria['sColumns']);
        } 
        if (array_key_exists('iSortingCols', $criteria) && is_array($this->columns)) {
            for ($c =0; $c < $criteria['iSortingCols'];$c++) {
                $sort = ' ASC';
                if (array_key_exists('sSortDir_'.$c, $criteria) && $criteria['sSortDir_'.$c] != '') {
                   switch (strtoupper($criteria['sSortDir_'.$c])) {
                      case 'ASC':
                      case 'E':   
                      case 'C':
                         $criteria['sSortDir_'.$c]='ASC';
                      break;
                      case 'DESC':
                      case 'D':
                      case 'S':
                         $criteria['sSortDir_'.$c]='DESC';
                      break;
                   }
                   $sort = ' '.$criteria['sSortDir_'.$c];
                }
                if (
                        array_key_exists('iSortCol_'.$c,$criteria) &&
                        array_key_exists($criteria['iSortCol_'.$c], $this->columns) &&
                        $this->columns[$criteria['iSortCol_'.$c]] != '') {
                   if (strpos($this->columns[$criteria['iSortCol_'.$c]],'.') === false)
                     $select->order($this->content->getTable()->getTable().'.'.$this->columns[$criteria['iSortCol_'.$c]].$sort);
                   else
                     $select->order($this->columns[$criteria['iSortCol_'.$c]].$sort);
                }
            }
        }
        if (
                array_key_exists('iDisplayStart',$criteria ) ||
                array_key_exists('iDisplayLength',$criteria )
            )
        $select->limit($criteria['iDisplayLength']);
        if (
                array_key_exists('iDisplayStart', $criteria) &&
                $criteria['iDisplayStart']>0
            )
         $select->offset($criteria['iDisplayStart']);
        $propertyName = null;
        if (property_exists($select,'storeToCache'))
            $propertyName=$select->storeToCache;  
        if (
               !is_null($propertyName) && 
               $this->content->getCache()->getItem($select->storeToCache) != ''
            ) {
            $this->items = $this->content->getCache()->getItem($select->storeToCache);
        } else {        
            try{
                $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
                $results = $statement->execute();
                $resultSet = new \Zend\Db\ResultSet\ResultSet();
                $resultSet->initialize($results);
                $data = $resultSet->toArray(); 
            }
            catch (\Exception $e) {
               $mysqli = $this->content->getTable()->getAdapter()->getDriver()->getConnection()->getResource();  
               if (array_key_exists('firephp', $GLOBALS) && !headers_sent())
                   $GLOBALS['firephp']->error('Error in '. get_called_class().' on query '.$select->getSqlString($this->content->getTable()->getAdapter()->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error);
               throw new \Exception('Error in '. get_called_class().' on query '.$select->getSqlString($this->content->getTable()->getAdapter()->getPlatform()).' '.$e->getMessage().' '.$mysqli->errno.' '.$mysqli->error,1401301242);
            }
            if(!headers_sent()) {
               //$GLOBALS['firephp']->log($select->getSqlString($this->content->getTable()->getAdapter()->getPlatform()));
            }
            $this->items=array();
            foreach($data as $dataitem) {
                $item = clone $this->content;
                //$GLOBALS['firephp']->log($dataitem);
                $item->setData($dataitem);
                array_push($this->items, $item);
            }
        } 
         if (
                property_exists($select,'storeToCache') && 
                $this->content->getCache()->getItem($select->storeToCache) == ''
             ) {
                $this->content->getCache()->setItem($select->storeToCache,$this->items);
         }
    }
    /**
   * Returns the collection items
   * @return array
   */
    public function getItems() {
        //$this->items = array_values($this->items);
        return $this->items;
    }
     /**
* Returns the collection items
* @return array
*/
    public function count() {
        return sizeof($this->items);
    }
    /**
* Returns all contents without any filter
*/
    public function countAll() {
      $select = $this->content->getTable()->getSql()->select()->columns(array(new \Zend\Db\Sql\Expression('COUNT(*)')));
      $statement = $this->content->getTable()->getSql()->prepareStatementForSqlObject($select);
      $results = $statement->execute();
      $resultSet = new \Zend\Db\ResultSet\ResultSet();
      $resultSet->initialize($results);
      $data = $resultSet->current()->getArrayCopy();
      return intval(array_pop($data));
      
    }
    /**
   * Return the colum names
   */
    public function getColumns() {
        return $this->columns;
    }
    /**
   * Add new item to the collection
   * @return \Content
   */
    public function addItem($key = null) {
        $item = clone $this->content;
        if (is_null($key))
           $this->items[]=$item;
        else {
           $this->items[$key]=$item;
           ksort($this->items);
        }
        return $item;
    }
     /**
     * Prepend an item
     * @param Content $item
     */
    public function prependItem(& $item) {
       array_unshift($this->items, $item);
    }
    /**
     * Append an item
     * @param Content $item
     */
    public function appendItem(& $item) {
       array_push($this->items, $item);
    }
    /**
   * Return the first item of the collection
   * @return \Content
   */
    public function getFirst() {
        if (!array_key_exists(0, $this->items))
            return $this->addItem ();
        return $this->items[0];
    }
    /**
   * Removes an itme by its id
   * @param type $key int
   */
    public function deleteByKey($key) {
        if (array_key_exists($key, $this->items))
            unset ($this->items[$key]);
    }
    /**
     * Empties the collection
     */
    public function emptyColl() {
       $this->items=array();
    }
    /**
     * Filter collection by attribute and value
     * @param mixed $value
     * @param mixed $field
     * @return \ContentColl
     */
    public function filterByAttributeValue($value,$field) {
       $filteredColl = clone $this;
       $filteredColl->emptyColl();
       foreach ($this->items as $item) {
          if ($item->getRawData($field) == $value)
             $filteredColl->appendItem ($item);
       }
       return $filteredColl;
    }
    /**
     * Sort teh array with user defined function
     * @param array $criteria
     */
    public function sort(array $criteria) {
       self::$sortCriteria = $criteria;
       usort($this->items, array(get_class($this),'customSort'));
    }
    /**
     * Shuffles the items
     */
    public function shuffle () {
       shuffle($this->items);
    }
    /**
     * Get fields as array
     * @param string $field
     * @return array
     */
    public function getFieldsAsArray($field) {
       $values = array();
       foreach ($this->items as $item) {
          $values[] = $item->getRawData($field);
       }
       return $values;  
    }
}