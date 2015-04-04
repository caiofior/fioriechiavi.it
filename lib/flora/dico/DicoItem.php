<?php
namespace flora\dico;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Taka dicotomic key item class
 *
 * @author caiofior
 */
class DicoItem extends \Content
{
   const maxCode='1';
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      set_error_handler(create_function('', ''),E_USER_WARNING);
      parent::__construct($db, 'dico_item');
      restore_error_handler();
   }
   /**
    * Not usable
    * @param int $id
    * @throws \Exception
    * @see loadFromIdAndDico
    */
   public function loadFromId ($id) {
      throw new \Exception('Deprecated see loadFromIdAndtaxa',1509141504);
   }
   /**
    * Load data fro dico id and id
    * @param type $dico_id
    * @param type $id
    */
   public function loadFromIdAndTaxa($parent_taxa_id,$id) {
      $this->data['parent_taxa_id']=$parent_taxa_id;
      $this->rawData['parent_taxa_id']=$parent_taxa_id;
      $this->data['id']=$id;
      $this->rawData['id']=$id;
      $data = $this->table->select(array(
          'parent_taxa_id'=>$this->data['parent_taxa_id'],
          'id'=>$this->data['id']
      ))->current();
      if (is_object($data)) {
          $this->data = array_filter($data->getArrayCopy(),  create_function('$val', 'return !is_null($val);'));
          $this->rawData = $this->data;
      } else {
         $this->data = array (
             'id'=>$id,
             'parent_taxa_id'=>$parent_taxa_id,
             'text'=>''
             );
         $this->rawData = $this->data;
      }
   }
   /**
    * Not usable
    * @param int $id
    * @throws \Exception
    * @see replace
    */
   public function update() {
      throw new \Exception('Deprecated see replace',1509141504);
   }
   /**
    * Not usable
    * @param int $id
    * @throws \Exception
    * @see replace
    */
   public function insert() {
      throw new \Exception('Deprecated see replace',1509141504);
   }
    /**
     * Replaces dico item data
     */
   public function replace() {
        if (
                !array_key_exists('parent_taxa_id', $this->data) &&
                array_key_exists('parent_taxa_id', $this->rawData)
                ) {
        $this->data['parent_taxa_id'] = $this->rawData['parent_taxa_id'];
        }
        if (array_key_exists('taxa_id',$this->rawData)) {
            $this->db->query('REPLACE  INTO `'.$this->table->getTable().'` 
            (id,parent_taxa_id,text,taxa_id)
            VALUES
            ("'.addslashes($this->rawData['id']).'",'.intval($this->rawData['parent_taxa_id']).',"'.  addslashes($this->rawData['text']).'",'.intval($this->rawData['taxa_id']).')
            ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        } else {
            $this->db->query('REPLACE  INTO `'.$this->table->getTable().'` 
            (id,parent_taxa_id,text)
            VALUES
            ("'.addslashes($this->rawData['id']).'",'.intval($this->rawData['parent_taxa_id']).',"'.  addslashes($this->rawData['text']).'")
            ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
   }
   /**
    * Deletes a dico item
    */
   public function delete() {
       if (
               array_key_exists('id', $this->rawData) && 
               $this->rawData['id'] != '' &&
               array_key_exists('parent_taxa_id', $this->rawData) && 
               $this->rawData['parent_taxa_id'] != ''
           ) {
            $this->db->query('DELETE FROM `'.$this->table->getTable().'` 
              WHERE `id`="'.addslashes($this->rawData['id']).'"
              AND `parent_taxa_id`='.intval($this->rawData['parent_taxa_id']),
            \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
           }
   }
   /**
    * Returns the sibling code
    * @return string
    */
   public function getSiblingCode() {
      $siblingCode = substr($this->rawData['id'],0,-1);
      $lastCode = substr($this->rawData['id'],0,1)+1;
      if ($lastCode>self::maxCode) 
         $lastCode = 0;
      $siblingCode .= $lastCode;
      return $siblingCode;
   }
   /**
    * Returns an array with childen codes
    * @return array
    */
   public function getChildrenCodeArray () {
      $childrenCodeArray = array();
      for ($c =0; $c <= self::maxCode; $c++)
         $childrenCodeArray[]=$this->rawData['id'].$c;
      return $childrenCodeArray;
   }
   /**
    * Gets the associated taxa
    * @return \flora\taxa\Taxa
    */
   public function getTaxa() {
      $taxa = new \flora\taxa\Taxa($this->db);
      if (array_key_exists('taxa_id',$this->rawData) && $this->rawData['taxa_id'] != '') {
         $taxa->loadFromId($this->rawData['taxa_id']);
      }
      return $taxa;
   }
    /**
    * Removes the association with taxa
    */
   public function removesTaxaAssociation() {
      unset($this->rawData['taxa_id']);
      $this->replace();
   }
}