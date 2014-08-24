<?php

error_reporting(E_ALL);

/**
 * FloraDItalia - flora/dico/class.DicoItem.php
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
 * @subpackage dico
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
 * include flora_dico_DicoItemColl
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('flora/dico/class.DicoItemColl.php');

/* user defined includes */
// section 127-0-1-1-651afd3b:147fc7005b0:-8000:00000000000008A6-includes begin
// section 127-0-1-1-651afd3b:147fc7005b0:-8000:00000000000008A6-includes end

/* user defined constants */
// section 127-0-1-1-651afd3b:147fc7005b0:-8000:00000000000008A6-constants begin
// section 127-0-1-1-651afd3b:147fc7005b0:-8000:00000000000008A6-constants end

/**
 * Short description of class flora_dico_DicoItem
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package flora
 * @subpackage dico
 */
class flora_dico_DicoItem
    extends core_Content
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getTaxa
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return flora_taxa_Taxa
     */
    public function getTaxa()
    {
        $returnValue = null;

        // section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000A9F begin
        // section 127-0-1-1--6479ccc9:147fd03277b:-8000:0000000000000A9F end

        return $returnValue;
    }

} /* end of class flora_dico_DicoItem */

?>