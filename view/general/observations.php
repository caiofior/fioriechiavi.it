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
if ($GLOBALS['db']->config->mapBoxToken != '') : ?>
<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
  integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
  crossorigin=""></script>
<script src='https://api.mapbox.com/mapbox-gl-js/v0.46.0/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v0.46.0/mapbox-gl.css' rel='stylesheet' />
<?php endif; ?>
<script>
    var mapBoxToken = "<?php echo $GLOBALS['db']->config->mapBoxToken; ?>";
    var points = <?php echo $pointsString; ?>;
    var latitude = <?php echo $centroid->y(); ?>;
    var longitude = <?php echo $centroid->x(); ?>;
    var radius = <?php echo intval($radius); ?>;
</script>
<?php 
endif;

