<?php
$from = preg_replace('/[^0-9]/','',$attributeColl->filterByAttributeValue('Limite altitudinale inferiore','name')->getFirst()->getRawData('value'));
$to = preg_replace('/[^0-9]/','',$attributeColl->filterByAttributeValue('Limite altitudinale superiore','name')->getFirst()->getRawData('value'));
?>
<p>Limite altitudinale
   da <?php echo number_format($from, 0,',', '.');?> m.
 a <?php echo number_format($to, 0,',', '.');?> m.
   : </p>
<div class="altitudeContainer">
   <img class="altitudeBase" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/altitude/altitudine.png" alt="Altitudine"/>
   <?php
   for($c = intval(1+($from/$GLOBALS['db']->config->attributes->altitudeStep)); $c <= intval(1+($to/$GLOBALS['db']->config->attributes->altitudeStep));$c++) : ?>
   <img class="altitude" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/altitude/<?php echo $c*$GLOBALS['db']->config->attributes->altitudeStep;?>.png" alt="<?php echo $c*$GLOBALS['db']->config->attributes->altitudeStep;?>"/>
   <?php endfor; ?>
</div>