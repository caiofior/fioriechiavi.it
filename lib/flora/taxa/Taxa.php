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
     * Short description of method getTaxaKind
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_taxa_TaxaKind
     */
    public function getTaxaKind()
    {
        $returnValue = null;

        // section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000AAC begin
        // section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000AAC end

        return $returnValue;
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