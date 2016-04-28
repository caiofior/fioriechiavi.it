<?php
if(!array_key_exists('db', $GLOBALS)) {
    require __DIR__.'/../include/pageboot.php';
}
$mysql = $GLOBALS['db']->getDriver()->getConnection()->getResource();
$wordH = fopen(__DIR__.'/words.txt', 'r');
$baseDir = __DIR__.'/../db/search';
if (!is_dir($baseDir)) {
   mkdir($baseDir);
}
while (($word = fgets($wordH)) !== false) {
   $word = iconv('UTF-8', 'ISO-8859-1',trim($word));
   $taxaRes = $mysql->query('SELECT taxa.id ,
   taxa.name ,
   (SELECT `initials` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) as taxa_kind_initials,
   (SELECT `name` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) as taxa_kind_name   
   FROM taxa_search
   LEFT JOIN taxa ON taxa.id=taxa_search.taxa_id
   WHERE MATCH (taxa_search.text) AGAINST ( "'.addslashes($word).'" IN NATURAL LANGUAGE MODE)
   LIMIT 10
   ');
   $baseDir = __DIR__.'/../db/search/'.substr($word, 0,1);
   if (!is_dir($baseDir)) {
      mkdir($baseDir);
   }
   $baseDir .= '/'.substr($word, 1,1);
   if (!is_dir($baseDir)) {
      mkdir($baseDir);
   }
   $baseDir .= '/'.$word.'.json';
   $searchResult=array();
   while ($taxa = $taxaRes->fetch_object()) {
      $searchResult[]=$taxa;
   }
   file_put_contents($baseDir,json_encode($searchResult,JSON_FORCE_OBJECT));
}
fclose($wordH);
$taxaRes = $mysql->query('SELECT * ,
   (SELECT `initials` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) as taxa_kind_initials,
   (SELECT `name` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) as taxa_kind_name,            
   (               
      IFNULL(LENGTH(taxa.description),0)+
      IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
      IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
      IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
   ) > 0 as status,
   (SELECT `parent_taxa_id` FROM `dico_item` WHERE `dico_item`.`taxa_id`=`taxa`.`id` LIMIT 1) as parent_taxa_id,
   (SELECT pt.name FROM taxa pt WHERE pt.id=(SELECT `parent_taxa_id` FROM `dico_item` WHERE `dico_item`.`taxa_id`=`taxa`.`id` LIMIT 1)) as parent_taxa_name,
   (SELECT name FROM taxa_kind WHERE taxa_kind.id=(SELECT pt.taxa_kind_id FROM taxa pt WHERE pt.id=(SELECT `parent_taxa_id` FROM `dico_item` WHERE `dico_item`.`taxa_id`=`taxa`.`id` LIMIT 1))) as parent_taxa_initials
   FROM taxa');

$baseDir = __DIR__.'/../db/taxa';
if (!is_dir($baseDir)) {
   mkdir($baseDir);
}
while ($taxa = $taxaRes->fetch_object()) {
   
   $dicoRes = $mysql->query('SELECT 
      dico_item.taxa_id,      
      dico_item.text,
      taxa.name,
      (SELECT `initials` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) as taxa_kind_initials,
      (SELECT `name` FROM `taxa_kind` WHERE `taxa_kind`.`id`=`taxa`.`taxa_kind_id` ) as taxa_kind_name,            
      (               
         IFNULL(LENGTH(taxa.description),0)+
         IFNULL((SELECT COUNT(`value`) FROM `taxa_attribute_value` WHERE `taxa_attribute_value`.`taxa_id`=`taxa`.`id`),0)+
         IFNULL((SELECT COUNT(`filename`) FROM `taxa_image` WHERE `taxa_image`.`taxa_id`=`taxa`.`id`),0)+
         IFNULL((SELECT COUNT(`id`) FROM `dico_item` WHERE `dico_item`.`parent_taxa_id`=`taxa`.`id`),0)
      ) > 0 as status
      FROM dico_item
      LEFT JOIN taxa ON taxa.id=dico_item.taxa_id
      WHERE parent_taxa_id='.$taxa->id);
   $taxa->dico=array();
   while ($dico = $dicoRes->fetch_object()) {
      $taxa->dico[]=$dico;
   }
   
   $imageRes = $mysql->query('SELECT * FROM taxa_image WHERE taxa_id='.$taxa->id);
   $taxa->image=array();
   while ($image = $imageRes->fetch_object()) {
      $taxa->image[]=$image;
   }
   
   $attributeRes = $mysql->query('SELECT name,value FROM taxa_attribute_value
      LEFT JOIN taxa_attribute ON taxa_attribute.id=taxa_attribute_value.taxa_attribute_id
      WHERE taxa_id='.$taxa->id);
   $taxa->attribute=array();
   while ($attribute = $attributeRes->fetch_object()) {
      $taxa->attribute[]=$attribute;
   }
   
   $regionRes = $mysql->query('SELECT name FROM taxa_region
      LEFT JOIN region ON region.id=taxa_region.region_id
      WHERE taxa_id='.$taxa->id);
   $taxa->region=array();
   while ($region = $regionRes->fetch_object()) {
      $taxa->region[]=$region;
   }
   $thousand = intval($taxa->id/1000);
   if (!is_dir($baseDir.'/'.$thousand)) {
      mkdir($baseDir.'/'.$thousand);
   }
   file_put_contents($baseDir.'/'.$thousand.'/'.$taxa->id.'.json',json_encode($taxa,JSON_FORCE_OBJECT));
}
