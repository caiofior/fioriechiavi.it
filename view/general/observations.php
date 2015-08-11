<?php
$taxaObservationColl = $taxa->getTaxaObservationColl(array(
    'valid'=>1,
    'iDisplayStart'=>0,
    'iDisplayLength'=>10,
    'sColumns'=>'datetime',
    'iSortingCols'=>'1',
    'iSortCol_0'=>'0',
    'sSortDir_0'=>'DESC',
    ));
if ($taxaObservationColl->count() > 0) : 
$pointsString='[';?>
<h2>Osservazioni</h2>
<div id="map-canvas" style="width: 100%; height: 400px;"></div>
<div class="observationColl">
<?php foreach($taxaObservationColl->getItems() as $taxaObservation) :
    if (is_object($taxaObservation->getPoint()) && $taxaObservation->getPoint()->x() != '' && $taxaObservation->getPoint()->y() != '') {
        $pointsString .= '{latitude:'.$taxaObservation->getPoint()->x().',longitude:'.$taxaObservation->getPoint()->y().'},';
    }
    $taxaObservationImage = $taxaObservation->getTaxaObservationImageColl(array('iDisplayStart'=>0,'iDisplayLength'=>1))->getFirst(); ?>
<div class="item">
    <p><strong><?php echo $taxaObservation->getData('title');?></strong></p>
    <a class="fancybox" href="<?php echo $taxaObservationImage->getUrl(); ?>">
    <img src="<?php echo $taxaObservationImage->getUrl(array('x'=>300,'y'=>200)); ?>">
    </a>
</div>
<?php endforeach; ?>
</div>
<?php $pointsString .=']'; 
$centroid = $taxaObservationColl->getMultiPoint()->centroid(); ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GLOBALS['db']->config->search->key; ?>"></script>
<script>
    points = <?php echo $pointsString; ?>;
    centroid = {latitude:<?php echo $centroid->x(); ?>,longitude:<?php echo $centroid->y(); ?>};
</script>
<?php 
endif;

