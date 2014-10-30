<?php
namespace flora\taxa;
/**
 * Taka class
 *
 * @author caiofior
 */
class TaxaAttribute extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa_attribute');
   }
   /**
    * Loads the attribute from its name
    * @param string $name
    */
   public function loadFromName($name) {
      $data = $this->table->select(array('name'=>$name))->current();
      if (is_object($data))
         $this->data = $data->getArrayCopy();
   }
   /**
    * Gets all attributes
    * @param array $criteria filter criteria
    * @return array
    */
   public function getAllValues($criteria)  {
        $sql = 'SELECT `value` FROM `taxa_attribute_value` WHERE TRUE ';

        if (
                array_key_exists('sSearch', $criteria) &&
                $criteria['sSearch'] != ''
            )
        $sql .= ' AND `value` LIKE "'.  addslashes($criteria['sSearch']).'%"';
        $sql .= ' AND `id_taxa_attribute`='.intval($this->data['id']);
	$sql .= ' GROUP BY `value`';
        if (
             array_key_exists('iDisplayStart',$criteria ) &&
             array_key_exists('iDisplayLength',$criteria )
         )
            $sql .= ' LIMIT '.intval($criteria['iDisplayStart']).','.intval($criteria['iDisplayLength']);
        $resultSet =  $this->db->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet->toArray();
   }
   /**
    * Deletes a single value
    * @param string $value
    */
   public function deleteValue ($value) {
       $this->db->query('
          DELETE FROM `taxa_attribute_value`
          WHERE  `id_taxa_attribute`='.intval($this->data['id']).'
          AND `value` = "'.addslashes($value).'"
          ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
   }
   /**
    * Replaces an attribute value
    * @param string $oldValue
    * @param string $value
    * @return string
    */
   public function replaceValue($oldValue,$value) {
          $this->db->query('
          UPDATE `taxa_attribute_value`
          SET `value` = "'.addslashes($value).'"
          WHERE  `id_taxa_attribute`='.intval($this->data['id']).'
          AND `value` = "'.addslashes($oldValue).'"
          ', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
          return $value;
   }
}