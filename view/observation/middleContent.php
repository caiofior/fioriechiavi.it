<?php
$taxaObservationColl = $this->object;
$pageBuffer=5;
$lastPage = intval($taxaObservationColl->countAll()/$_REQUEST['pagelength']);
$GLOBALS['lastPage']=$lastPage;
$currentpage=min($lastPage,intval($_REQUEST['start']/$_REQUEST['pagelength']));
$pages =array(0,$lastPage);
$pages = array_merge($pages,range($currentpage-$pageBuffer,$currentpage+$pageBuffer));
$pages=array_unique($pages);
$pages=array_filter($pages, function($val){return $val >= 0 && $val <=$GLOBALS['lastPage'];});
sort($pages);
?>
<div id="paginationContainer">
<?php foreach($pages as $page):
    $class='';
    if ($page == $currentpage) {
        $class=' selectedPage';
    }
    ?>
    <a href="?start=<?php echo $page*$_REQUEST['pagelength'].(array_key_exists('text', $_REQUEST) ? '&amp;text='.$_REQUEST['text']: '')?>" class="pageSelector<?php echo $class;?>" data-page="<?php echo $page*$_REQUEST['pagelength'];?>"><?php echo $page+1;?></a>
    <?php if ($page <$lastPage && !in_array($page+1, $pages)) :?>…<?php endif;?>
<?php endforeach; ?>
</div>
<div id="map-canvas" style="width: 100%; height: 400px;"></div>
<?php
$radius = 12;
$pointsString='[';
foreach ($taxaObservationColl->getItems() as $index=>$taxaObservation):
    $taxa = $taxaObservation->getTaxa();
    if (is_object($taxaObservation->getPoint()) && $taxaObservation->getPoint()->y() != '' && $taxaObservation->getPoint()->x() != '') {
        $pointsString .= '{latitude:'.$taxaObservation->getPoint()->y().',longitude:'.$taxaObservation->getPoint()->x().'},';
    } ?>
<div>
    <p><strong><?php echo chr(65+$index); ?>) <a href="<?php echo $GLOBALS['db']->config->baseUrl;?>observation.php?id=<?php echo $taxaObservation->getData('id');?>"><?php echo $taxa->getData('taxa_kind_initials'); ?> <?php echo $taxa->getData('name'); ?></a></strong></p>
    <p><em><?php echo $taxaObservation->getData('title'); ?></em></p>
    <p><?php echo strftime('%e  %B  %Y',date_format(new DateTime($taxaObservation->getData('datetime')),'U')); ?></p>
    <p><?php echo $taxaObservation->getData('description'); ?></p>
    <?php $taxaObservationImage = $taxaObservation->getTaxaObservationImageColl(array('iDisplayStart'=>0,'iDisplayLength'=>1))->getFirst();
    $thumbnailImageUrl = null;
    try {
    $thumbnailImageUrl = $taxaObservationImage->getUrl(array('x'=>300,'y'=>200));
    } catch (Exception $e) {}
    if (!is_null($thumbnailImageUrl)) : ?>
    <a class="fancybox" href="<?php echo $taxaObservationImage->getUrl(); ?>">
    <img src="<?php echo $thumbnailImageUrl; ?>">
    </a>
    <?php endif; ?>
</div>
<?php endforeach;
if ($taxaObservationColl->count() > 1) {
    $radius -= sqrt($taxaObservationColl->getMultiPoint()->envelope()->area())^2;
}
$pointsString .=']'; 
$centroid = $taxaObservationColl->getMultiPoint()->centroid();
if (is_object($GLOBALS['db']->config->search) && $GLOBALS['db']->config->search->key != '') : ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GLOBALS['db']->config->search->key; ?>"></script>
<?php endif; ?>
<script>
    points = <?php echo $pointsString; ?>;
    centroid = {latitude:<?php echo $centroid->y(); ?>,longitude:<?php echo $centroid->x(); ?>};
    radius = <?php echo $radius; ?>;
    updateMap();
</script>

