<?php
namespace flora\dico\import;
/**
 * Imports the data in the Pignatti flora Italica Format
 */
class Pignatti implements \flora\dico\import\Import {
   /**
    * imports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function import (\flora\dico\DicoItemIntColl $dicoItemColl, $stream) {
      
      stream_filter_register('pignatti_input', 'flora\dico\import\pignatti_input_filter');
      stream_filter_append($stream, 'pignatti_input');
      $dicoItemColl->emptyColl();
      $positions = array();
      $lastPosition = '';
      $cols = array('id','text','taxa_id');
      while($row = fgetcsv($stream,1000,"\t")) {
         while (sizeof($row)<sizeof($cols)) {
            $row[]='';
         }
         while (sizeof($row)>sizeof($cols)) {
            array_pop($row);
         }
         $row = array_combine($cols, $row);
         $row['id']=trim($row['id']);
         if (!is_numeric($row['id'])) {
            $dicoItemColl->errors = new \stdClass();
            $dicoItemColl->errors->message = 'There is one item without id';
            $dicoItemColl->errors->code = 1410141231;
         }
         if ($row['text']=='') {
            $dicoItemColl->errors = new \stdClass();
            $dicoItemColl->errors->message = 'There is one item without text';
            $dicoItemColl->errors->code = 1410141231;
         }
         if (!array_key_exists($row['id'], $positions)) {
            $positions[$row['id']]=array();
            $positions[$row['id']][]=$lastPosition.'0';
            $positions[$row['id']][]=$lastPosition.'1';
         }
         $row['id'] = $lastPosition = array_shift($positions[$row['id']]);
         if (is_null($row['id'])) {
            $row['incomplete']=true;
            $dicoItemColl->errors = new \stdClass();
            $dicoItemColl->errors->message = 'There is one item with more than two questions';
            $dicoItemColl->errors->code = 1410141232;
         }
         if (!is_numeric($row['taxa_id'])) {
            $row['taxa_id']=null;
         }
         $dicoItem = $dicoItemColl->addItem();
         $dicoItem->setData($row);
      }
      if (sizeof(array_filter($positions))>0) {
         $dicoItemColl->errors = new \stdClass();
         $dicoItemColl->errors->message = 'There are some dico items not well coupled';
         $dicoItemColl->errors->code = 1410141230;
         $dicoItemColl->errors->items = implode(', ', array_filter(array_keys(array_filter($positions))));
      }
      return $dicoItemColl;
   }
}

class pignatti_input_filter extends \php_user_filter {
  function filter($in, $out, &$consumed, $closing)
  {
    while ($bucket = stream_bucket_make_writeable($in)) {
      $data = preg_replace('/^[ ]*([0-9]*)[ ]*/m',"$0\t",$bucket->data);
      $data = preg_replace('/[ ]*[\.]{3,}[ ]*/m',"\t",$data);
      $bucket->data = $data;
      $consumed += $bucket->datalen;
      stream_bucket_append($out, $bucket);
    }
    return PSFS_PASS_ON;
  }
}

