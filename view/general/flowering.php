<p>Fioritura
 da <?php echo $attributeColl->filterByAttributeValue('Inizio fioritura','name')->getFirst()->getRawData('value')?>
 a <?php echo $attributeColl->filterByAttributeValue('Fine fioritura','name')->getFirst()->getRawData('value')?>
   : </p>
<div class="floweringContainer">
   <img class="flowerMonth" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/flower/months.png" alt="Mesi"/>
   <?php
   $months = array();
   $nameToNumber=$GLOBALS['db']->config->attributes->floweringNames->toArray();
   $from = $nameToNumber[$attributeColl->filterByAttributeValue('Inizio fioritura','name')->getFirst()->getRawData('value')];
   $to = $nameToNumber[$attributeColl->filterByAttributeValue('Fine fioritura','name')->getFirst()->getRawData('value')];
   if ($from <= $to):	
   for($c = $from ; $c <= $to ;$c++) : ?>
   <img class="flowerMonth" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/flower/<?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>.png" alt="Mese <?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>"/>
   <?php endfor;
   else :
   for($c = $from ; $c <= 12 ;$c++) : ?>
   <img class="flowerMonth" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/flower/<?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>.png" alt="Mese <?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>"/>
   <?php endfor;
   for($c = 1 ; $c <= $to ;$c++) : ?>
   <img class="flowerMonth" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/flower/<?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>.png" alt="Mese <?php echo str_pad($c,2,'0',STR_PAD_LEFT);?>"/>
   <?php endfor;
   endif;
   ?>
</div>