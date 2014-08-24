<?php

error_reporting(E_ALL);

/**
 * FloraDItalia - flora/taxa/class.Taxa.php
 *
 * $Id$
 *
 * This file is part of FloraDItalia.
 *
 * Automatically generated on 23.08.2014, 08:39:04 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package flora
 * @subpackage taxa
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include core_Content
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('core/class.Content.php');

/**
 * include flora_dico_DicoItem
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('flora/dico/class.DicoItem.php');

/**
 * include flora_region_RegionColl
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('flora/region/class.RegionColl.php');

/**
 * include flora_taxa_TaxaAttributeColl
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('flora/taxa/class.TaxaAttributeColl.php');

/**
 * include flora_taxa_TaxaImageColl
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('flora/taxa/class.TaxaImageColl.php');

/**
 * include flora_taxa_TaxaKind
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('flora/taxa/class.TaxaKind.php');

/* user defined includes */
// section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000A9C-includes begin
// section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000A9C-includes end

/* user defined constants */
// section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000A9C-constants begin
// section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000A9C-constants end

/**
 * Short description of class flora_taxa_Taxa
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package flora
 * @subpackage taxa
 */
class flora_taxa_Taxa
    extends core_Content
{
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