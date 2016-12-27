<?php
namespace flora\linkprovider\provider;
/**
 * Get plant reference from actaplanctorum site
 */
class Florae implements \flora\linkprovider\provider\Provider {
   /**
    * Get plant reference from actaplanctorum site
    */
   public function retrive (\flora\taxa\Taxa $taxa) {
      $url = 'http://www.florae.it/xhr.php?task=taxa&action=search&name='.urlencode($taxa->getData('name')).'&col_id='.urlencode($taxa->getData('col_id')).'&eol_id='.urlencode($taxa->getData('eol_id'));
      $ch = curl_init($url);
      curl_setopt_array($ch,array(
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_CONNECTTIMEOUT => 10,
           CURLOPT_TIMEOUT => 10,
      ));
      $response = curl_exec($ch);
      if ($response == '') {
         return false;
      } else {
         return json_decode($response);
      }
   }
}

