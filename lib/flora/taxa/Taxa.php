<?php
namespace flora\taxa;
/**
 * Taka class
 *
 * @author caiofior
 */
class Taxa extends \Content
{
   /**
    * Associates the database table
    * @param \Zend\Db\Adapter\Adapter $db
    */
   public function __construct(\Zend\Db\Adapter\Adapter $db) {
      parent::__construct($db, 'taxa');
   }
   /**
      * Loads data from its id
      * @param int $id
      */
    public function loadFromId($id) {
       
         if (
         !is_object($this->table) ||
         !$this->table instanceof \Zend\Db\TableGateway\TableGateway
         ) {
            throw new \Exception('No database table associated with this content',1409011101);
        }
        $select =  $this->table->getSql()->select()
        ->where('`taxa`.`id` = '.intval($id))
        ->join('taxa_kind', 'taxa.taxa_kind_id=taxa_kind.id',array('taxa_kind_initials'=>'initials','taxa_kind_id_name'=>'name'), \Zend\Db\Sql\Select::JOIN_LEFT);
        $data = $this->table->selectWith($select)->current();
        if (is_object($data))
            $this->data = $data->getArrayCopy();
        
 
    }
    public function update() {
       unset($this->data['taxa_kind_initials']);
       unset($this->data['taxa_kind_id_name']);
       parent::update();
    }
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getRegionColl
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_region_RegionColl
     */
    public function getRegionColl()
    {
        $returnValue = null;

        // section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000AA4 begin
        // section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000AA4 end

        return $returnValue;
    }
    /**
     * Loads associated taxa kind
     * @return \flora\taxa\TaxaKind
     */
    public function getTaxaKind()
    {
        $taxaKind = new \flora\taxa\TaxaKind($this->db);
        if (array_key_exists('taxa_kind_id',$this->data) && $this->data['taxa_kind_id'] != '') {
         $taxaKind->loadFromId($this->data['taxa_kind_id']);
        }
        return $taxaKind;
    }
     /**
     * Loads associated dicotomic key
     * @return \flora\dico\Dico
     */
    public function getDico()
    {
        $dico = new \flora\dico\Dico($this->db);
        if (array_key_exists('dico_id',$this->data) && $this->data['dico_id'] != '') {
         $dico->loadFromId($this->data['dico_id']);
        }
        return $dico;
    }
    /**
     * Short description of method getTaxaImgeColl
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_taxa_TaxaImageColl
     */
    public function getTaxaImgeColl()
    {
        $returnValue = null;

        // section 127-0-1-1--5b6a38ee:147fe1dda19:-8000:0000000000000AC5 begin
        // section 127-0-1-1--5b6a38ee:147fe1dda19:-8000:0000000000000AC5 end

        return $returnValue;
    }

    /**
     * Short description of method getTaxaAttributeColl
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_taxa_TaxaAttributeColl
     */
    public function getTaxaAttributeColl()
    {
        $returnValue = null;

        // section 127-0-1-1--5b6a38ee:147fe1dda19:-8000:0000000000000ACF begin
        // section 127-0-1-1--5b6a38ee:147fe1dda19:-8000:0000000000000ACF end

        return $returnValue;
    }

} /* end of class flora_taxa_Taxa */

?>