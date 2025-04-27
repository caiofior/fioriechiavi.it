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
      $url = 'https://www.florae.it/xhr.php?task=taxa&action=search&name='.urlencode($taxa->getData('name')??'').'&col_id='.urlencode($taxa->getData('col_id')??'').'&eol_id='.urlencode($taxa->getData('eol_id')??'');
      $ch = curl_init($url);
      curl_setopt_array($ch,array(
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_CONNECTTIMEOUT => 10,
           CURLOPT_TIMEOUT => 10,
      ));
      $response = curl_exec($ch);
      if(curl_errno ($ch)>0) {
         return false;
      }
      if(curl_getinfo ($ch,CURLINFO_HTTP_CODE) != 200) {
         return false;
      }   
      if ($response == '') {
         return false;
      } else {
         $decodedResponse = json_decode($response);
         if (json_last_error() != 0) {
            return false;
         }
         return $decodedResponse; 
      }
   }
}

