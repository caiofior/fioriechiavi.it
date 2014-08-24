<?php
/**
* Content object
*
* Content object
*
* @author Claudio Fior <caiofior@gmail.com>
*/
/**
* Content object
*
* Content object
*
* @author Claudio Fior <caiofior@gmail.com>
*/
abstract class Content {
/**
* Zend DB
* @var Zend_Db
*/
    protected $db;   
/**
* Zend Data table
* @var Zend_Db_Table
*/
    protected $table;
    /**
* Data used for insert and update
* @var array
*/
    protected $data=array();
    /**
* Raw data, with cusom culoms
* @var array
*/
    protected $rawData=array();
    /**
* Columns available in database
* @var array
*/
    protected $empty_entity=array();
    /**
* Primary key
* @var string
*/
    protected $primary;
    /**
* Instantiates the table
* @param string $table
*/
    protected static $metadata;
    /**
     * Constructor
     * @param \Zend\Db\Adapter\Adapter $db
     * @param type $table
     * @return type
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db, $table=null) {
        $this->db = $db;
        if (is_null($table))
            return;
        $this->table = new \Zend\Db\TableGateway\TableGateway($table,$this->db);
        
        if (!is_array(self::$metadata)) {
           self::$metadata=array();
           $cacheData = '';
           if (
                   method_exists($this->db,'cache') && 
                   $this->db->cache instanceof Zend\Cache\Storage\Adapter
               )
              $cacheData = $this->db->cache->getItem('metadata');
           if ($cacheData != '') {
               self::$metadata=$cacheData;
           }
        }
        
        if (!key_exists($table, self::$metadata)) {
           
            self::$metadata[$table]=array(
                'columns'=>array(),
                'primaryKey'=>array()
            );
            
            $columns = $db->query('
             SHOW COLUMNS FROM `'.$table.'`'
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            
            if ($columns->count() == 0 )
              trigger_error ('Table with no columns'.$table,E_USER_WARNING);
            foreach ($columns->toArray() as $column ) {
               self::$metadata[$table]['columns'][]=$column['Field'];
            }
            
            $primary_key = $db->query('
             SHOW INDEX FROM `'.$table.'` WHERE Key_name="PRIMARY"'
            , \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            
            
            if ($primary_key->count() == 0 )
               trigger_error ('No primary key in table '.$table,E_USER_WARNING);
            else if ($primary_key->count() > 1 )
               trigger_error ('Primary key with multiple columns in table '.$table,E_USER_WARNING);
            
            $primary_key = $primary_key->current();
            self::$metadata[$table]['primaryKey']=  $primary_key['Column_name'];  
            if (
                method_exists($this->db,'cache') && 
                $this->db->cache instanceof Zend\Cache\Storage\Adapter
            )
            $this->db->cache->setItem('metadata',self::$metadata);
         }
            
      if (key_exists($table, self::$metadata)) {
         $this->primary = self::$metadata[$table]['primaryKey'];
         $cols =self::$metadata[$table]['columns'];
         $this->empty_entity = array_combine($cols, array_fill ( 0 , sizeof($cols) , null ));
     }
   }
    /**
      * Loads data from its id
      * @param int $id
      */
    public function loadFromId($id) {
        $data = $this->table->select(array($this->primary=>$id))->current();
        if (is_object($data))
            $this->data = $data->getArrayCopy();
    }
     /**
* Gets the data
* @param null|sring $field field data of interest
* @return array
*/
    public function getData($field = null) {
        if (!is_array($this->data))
            return;
        if (is_null($field))
            return $this->data;
        if (key_exists($field, $this->data))
            return $this->data[$field];
    }
     /**
* Gets the raw data
* @param null|sring $field field data of interest
* @return array
*/
    public function getRawData($field = null) {
        if (is_null($field))
            return $this->rawData;
        if (key_exists($field, $this->rawData))
            return $this->rawData[$field];
    }
    /**
* Sets the data
* @param variant $data
* @param string|null $field
*/
    public function setData($data,$field=null){
        if (is_array($data)) {
            $this->data = array_merge($this->data, array_intersect_key($data,$this->empty_entity));
            $this->rawData = array_merge($this->data, $data);
         }
        else if (!is_null($field) ) {
            if (array_key_exists($field,$this->empty_entity))
                $this->data[$field] = $data;
            $this->rawData[$field] = $data;
        }
    }
    /**
   * Adds a data
   */
    public function insert() {
        $id = $this->table->insert($this->data);
        if (is_null($id)) {
            if (key_exists('firephp', $GLOBALS))
               $GLOBALS['firephp']->error('Error on query '.$this->table->getSql()->getSqlPlatform()->getSqlString($this->table->getAdapter()->getPlatform()));
           throw new Exception('Error on query '.$this->table->getSql()->getSqlPlatform()->getSqlString($this->table->getAdapter()->getPlatform())) ;
        }
        $this->data[$this->primary]=$this->table->getLastInsertValue();
    }
     /**
   * Deletes data
   */
    public function delete() {
        if (key_exists($this->primary, $this->data)) {
            $this->table->delete(array($this->primary=>$this->data[$this->primary]));
        }
    }
     /**
   * Updates data
   */
    public function update() {
        if (!key_exists($this->primary, $this->data))
            throw new Exception('Unable to update object without id',1301251051);
        $data = $this->data;
        unset($data[$this->primary]);
        $this->table->update($data,array($this->primary=>$this->data[$this->primary]));
    }
    /**
   * Returns associated db table
   * @return Zend\Db\Db
   */
    public function getDb() {
        return $this->db;
    }
    /**
   * Returns associated db table
   * @return Zend\Db\TableGateway\TableGateway
   */
    public function getTable() {
        return $this->table;
    }
    /**
   * Is the Content empty
   * @return bool
   */
    public function isEmpty () {
        return sizeof($this->data) ==0;
    }
    /**
     * Returns the cache
     * @return type
     */
    public function getCache() {
       return $this->db->cache;
    }
}