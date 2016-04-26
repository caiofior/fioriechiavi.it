<?php
try{
    $taxaObservationColl = $taxa->getTaxaObservationColl(array(
    'valid'=>1,
    'iDisplayStart'=>0,
    'iDisplayLength'=>10,
    'sColumns'=>'datetime',
    'iSortingCols'=>'1',
    'iSortCol_0'=>'0',
    'sSortDir_0'=>'DESC',
    ));
} catch (\Exception $e ) {
    if ($e->getCode() != 1508121236) {
        throw $e;
    }
}
$radius = 12;
if ($taxaObservationColl->count() > 0) : 
$pointsString='[';?>
<h2>Osservazioni</h2>
<div id="map-canvas" style="width: 100%; height: 400px;"></div>
<div class="observationColl">
<?php foreach($taxaObservationColl->getItems() as $index => $taxaObservation) :
    if (is_object($taxaObservation->getPoint()) && $taxaObservation->getPoint()->y() != '' && $taxaObservation->getPoint()->x() != '') {
        $pointsString .= '{latitude:'.$taxaObservation->getPoint()->y().',longitude:'.$taxaObservation->getPoint()->x().'},';
    }
    $taxaObservationImage = $taxaObservation->getTaxaObservationImageColl(array('iDisplayStart'=>0,'iDisplayLength'=>1))->getFirst();
    $thumbnailImageUrl = null;
    try {
    $thumbnailImageUrl = $taxaObservationImage->getUrl(array('x'=>300,'y'=>200));
    } catch (Exception $e) {}
    if (!is_null($thumbnailImageUrl)) : ?>
<div class="item">
    <a id="observation_<?php echo $index;?>" href="<?php echo $GLOBALS['db']->config->baseUrl;?>observation.php?id=<?php echo $taxaObservation->getData('id');?>"><strong><?php echo chr(65+$index).') '.$taxaObservation->getData('title');?></strong></a>
    <a class="fancybox" href="<?php echo $taxaObservationImage->getUrl(); ?>">
    <img src="<?php echo $thumbnailImageUrl; ?>">
    </a>
</div>
<?php endif;
endforeach;
if ($taxaObservationColl->count() > 1) {
    $radius -= sqrt($taxaObservationColl->getMultiPoint()->envelope()->area())^3;
}
?>
</div>
<?php $pointsString .=']'; 
$centroid = $taxaObservationColl->getMultiPoint()->centroid();
if (is_object($GLOBALS['db']->config->search) && $GLOBALS['db']->config->search->key != '') : ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GLOBALS['db']->config->search->key; ?>"></script>
<?php endif; ?>
<script>
    points = <?php echo $pointsString; ?>;
    centroid = {latitude:<?php echo $centroid->y(); ?>,longitude:<?php echo $centroid->x(); ?>};
    radius = <?php echo $radius; ?>;
</script>
<?php 
endif;

