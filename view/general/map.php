<?php
$regionColl = $taxa->getRegionColl();
$regionColl = $regionColl->filterByAttributeValue('1','selected');
if ($regionColl->count() >0) : ?>
<p>Diffusione:</p>
<div class="mapContainer">
   <img class="baseMap" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/map/italia.png" alt="Italia"/>
   <?php foreach($regionColl->getItems() as $region) : ?>
   <img class="mapRegion" src="<?php echo $GLOBALS['db']->config->staticUrl;?>/images/map/<?php echo $region->getData('id');?>.png" alt="<?php echo $region->getData('name');?>"/>
   <?php endforeach; ?>
</div>
<div class="clear"></div>
<?php endif;