<?php
namespace flora\dico\import;
/**
 * Imports the data in the Pignatti flora Italica Format
 */
class Simonetti implements \flora\dico\import\Import {
   /**
    * imports the data in the stream
    * @param \flora\dico\DicoItemColl $dicoItemColl
    * @param resorice $stream
    */
   public function import (\flora\dico\DicoItemIntColl $dicoItemColl, $stream) {
      
      //stream_filter_register('simonetti_input', 'flora\dico\import\simonetti_input_filter');
      //stream_filter_append($stream, 'simonetti_input');
      $dicoItemColl->emptyColl();
      $positions = array();
      $lastPosition = '';
      $cols = array('id','text','taxa_id');
      while($string = fgets($stream,1000)) {
         $row = array_flip($cols);
         preg_match('/^\\-*/',$string,$position);
         $posNumber = strlen($position[0])+1;
         $row['id']= '';
         $string = preg_replace('/^\\-*/', '', $string);
         $string = trim(preg_replace('/:.*/', '', $string));
         $row['text']= $string;

         if ($row['text']=='') {
            $dicoItemColl->errors = new \stdClass();
            $dicoItemColl->errors->message = 'There is one item without text';
            $dicoItemColl->errors->code = 1410141231;
         }
         if (!array_key_exists($posNumber, $positions) || sizeof($positions[$posNumber]) == 0) {
            $positions[$posNumber]=array();
            $positions[$posNumber][]=$lastPosition.'0';
            $positions[$posNumber][]=$lastPosition.'1';
         }
         $row['id'] = $lastPosition = array_shift($positions[$posNumber]);
         $row['taxa_id']=null;
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

class simonetti_input_filter extends \php_user_filter {
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

