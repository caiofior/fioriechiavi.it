<?php
namespace flora\linkprovider\provider;
/**
 * Get plant reference from external site
 */
interface Provider {
    /**
    * Get plant reference from external site
    * @param \flora\taxa\Taxa $taxa
    */
   public function retrive (\flora\taxa\Taxa $taxa);
   
}
