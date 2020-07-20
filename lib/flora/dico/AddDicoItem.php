<?php
namespace flora\dico;
if (!class_exists('\Autoload')) {
   require __DIR__.'/../../core/Autoload.php';
   \Autoload::getInstance();
}
/**
 * Additional Taxa dicotomic key item class
 *
 * @author caiofior
 */
class AddDicoItem extends \Content implements \flora\dico\DicoItemInt
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      set_error_handler(function() {},E_USER_WARNING);
      parent::__construct($db, 'add_dico_item');
      restore_error_handler();
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
            !array_key_exists('id', $this->data) ||
            !array_key_exists('id', $this->rawData)
            ) {
               throw new \Exception('Dico item id is missing');
        }
        if (
                !array_key_exists('dico_id', $this->data) &&
                array_key_exists('dico_id', $this->rawData)
                ) {
        $this->data['dico_id'] = $this->rawData['dico_id'];
        }
        if (array_key_exists('taxa_id',$this->rawData)) {
            $this->db->query('REPLACE  INTO `'.$this->table->getTable().'` 
            (id,dico_id,text,taxa_id)
            VALUES
            ("'.addslashes($this->rawData['id']).'",'.intval($this->rawData['dico_id']).',"'.  addslashes($this->rawData['text']).'",'.intval($this->rawData['taxa_id']).')
            ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        } else {
            $this->db->query('REPLACE  INTO `'.$this->table->getTable().'` 
            (id,dico_id,text)
            VALUES
            ("'.addslashes($this->rawData['id']).'",'.intval($this->rawData['dico_id']).',"'.  addslashes($this->rawData['text']).'")
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
               array_key_exists('dico_id', $this->rawData) && 
               $this->rawData['dico_id'] != ''
           ) {
            $this->db->query('DELETE FROM `'.$this->table->getTable().'` 
              WHERE `id`="'.addslashes($this->rawData['id']).'"
              AND `dico_id`='.intval($this->rawData['dico_id']),
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
    * Load data fro dico id and id
    * @param type $dico_id
    * @param type $id
    */
   public function loadFromIdAndDico($dico_id,$id) {
      $this->data['dico_id']=$dico_id;
      $this->rawData['dico_id']=$dico_id;
      $this->data['id']=$id;
      $this->rawData['id']=$id;
      $data = $this->table->select(array(
          'dico_id'=>$this->data['dico_id'],
          'id'=>$this->data['id']
      ))->current();
      if (is_object($data)) {
          $this->data = array_filter($data->getArrayCopy(),  create_function('$val', 'return !is_null($val);'));
          $this->rawData = $this->data;
      } else {
         $this->data = array (
             'id'=>$id,
             'dico_id'=>$dico_id,
             'text'=>''
             );
         $this->rawData = $this->data;
      }
   }
   /**
    * Removes the association with taxa
    */
   public function removesTaxaAssociation() {
      unset($this->data['taxa_id']); 
      unset($this->rawData['taxa_id']);
      $this->replace();
   }
}