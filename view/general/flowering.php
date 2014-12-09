<p>Fioritura
 da <?php echo $attributeColl->filterByAttributeValue('Inizio fioritura','name')->getFirst()->getRawData('value')?>
 a <?php echo $attributeColl->filterByAttributeValue('Fine fioritura','name')->getFirst()->getRawData('value')?>
   : </p>
<div class="floweringContainer">
   <img class="flowerMonth" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/flower/months.png" alt="Mesi"/>
   <?php
   $months = array();
   $nameToNumber=array(
       'Gennaio'=>1,
       'Febbraio'=>2,
       'Marzo'=>3,
       'Aprile'=>4,
       'Maggio'=>5,
       'Giugno'=>6,
       'Luglio'=>7,
       'Agosto'=>8,
       'Settembre'=>9,
       'Ottobre'=>10,
       'Novembre'=>11,
       'Dicembre'=>12
   );
   $from = $nameToNumber[$attributeColl->filterByAttributeValue('Inizio fioritura','name')->getFirst()->getRawData('value')];
   $to = $nameToNumber[$attributeColl->filterByAttributeValue('Fine fioritura','name')->getFirst()->getRawData('value')];
   for($c = $from ; $c <= $to ;$c++) : ?>
   <img class="flowerMonth" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/flower/<?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>.png" alt="Mese <?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>"/>
   <?php endfor; ?>
</div>