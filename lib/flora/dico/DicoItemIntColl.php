<?php

namespace flora\dico;

/**
 * Description of Dicotomic key Coll
 *
 * @author caiofior
 */
Interface DicoItemIntColl {
    /**
     * Imports Dico Item from data stream
     *
     * @param type $stream
     * @throws \Exception
     */
    public function import($format, $stream);
    /**
     * Imports Dico Item from data stream and saves it
     *
     * @param type $stream
     * @throws \Exception
     */
    public function importAndSave($format, $stream);
    /**
     * Deletes all dico item associated
     */
    public function emptyDicoItems();
    /**
     * Filter collection by attribute and value
     * @param mixed $value
     * @param mixed $field
     * @return \flora\dico\DicoItemColl
     */
    public function filterByAttributeValue($value, $field);
    /**
     * Sort criteria
     * @param \flora\dico\DicoItem $a
     * @param \flora\dico\DicoItem $b
     * @return int
     */
    public static function customSort($a, $b);
}
