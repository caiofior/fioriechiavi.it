<?php
$filter = array(
    'iDisplayStart'=>0,
    'iDisplayLength'=>10,
    'sColumns'=>'datetime',
    'iSortingCols'=>'1',
    'iSortCol_0'=>'0',
    'sSortDir_0'=>'DESC',
    'valid'=>true
);
if (
        array_key_exists('key', $_REQUEST) &&
        $_REQUEST['key'] != ''
    ) {
    $id = $this->getTemplate()->decodeId($_REQUEST['key']);
    if (is_numeric($id)) {
        $profile = new login\user\Profile($GLOBALS['db']);
        $profile->loadFromId($id);
        if ($profile->getData('id')==$id) {
            unset($filter['valid']);
            if($profile->getData('role_id') == 3) {
                $filter['profile_id']=$profile->getData('id');
            }
        }
    }
}
$taxaObservationColl = new \floraobservation\TaxaObservationColl($GLOBALS['db']);
$taxaObservationColl->loadAll($filter);
$taxaObservation = $taxaObservationColl->getFirst();
$dateTime = date_create_from_format('Y-m-d H:i:s',$taxaObservation->getData('datetime'));
header('Content-Type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;?>
<rss version="2.0">
       <channel>
            <title><?php echo $GLOBALS['config']->siteName;?></title>
            <link><?php echo $GLOBALS['config']->baseUrl;?></link>
            <description>Un nome per ogni fiore</description>
            <language>it-it</language>
            <?php if (is_object($dateTime)) : 
                ?><lastBuildDate><?php echo $dateTime->format(DateTime::RSS);?></lastBuildDate><?php
            echo PHP_EOL; endif; ?>
            <docs>https://validator.w3.org/feed/docs/rss2.html</docs>
            <generator>fioriechiavi</generator>
            <managingEditor><?php echo $GLOBALS['config']->mail_from;?> (<?php echo $GLOBALS['config']->siteName;?>)</managingEditor>
            <webMaster>caiofior@gmail.com (Claudio Fior)</webMaster>
            <image>
                <url><?php echo $GLOBALS['config']->baseUrl;?>template/leaf/images/logo.jpg</url>
                <title>Un nome per ogni fiore</title>
                <link><?php echo $GLOBALS['config']->baseUrl; ?></link>
            </image>
<?php
foreach($taxaObservationColl->getItems() as $taxaObservation) :
    $taxa = $taxaObservation->getTaxa();
    $dateTime = date_create_from_format('Y-m-d H:i:s',$taxaObservation->getData('datetime'));
?>
            <item>
                <title><?php echo $taxa->getData('taxa_kind_initials'); ?> <?php echo $taxa->getData('name'); ?> - <?php echo $taxaObservation->getData('title'); ?></title>
                <?php if ($taxaObservation->getData('valid')==1) : 
                ?><link><?php echo $GLOBALS['config']->baseUrl;?>observation.php?id=<?php echo $taxaObservation->getData('id');?></link><?php
                echo PHP_EOL; endif; ?>
                <description><![CDATA[<?php
                $taxaObservationImage = $taxaObservation->getTaxaObservationImageColl(array('iDisplayStart'=>0,'iDisplayLength'=>1))->getFirst();
                $thumbnailImageUrl = null;
                try {
                $thumbnailImageUrl = $taxaObservationImage->getUrl(array('x'=>300,'y'=>200));
                } catch (Exception $e) {}
                if (!is_null($thumbnailImageUrl)) :
                ?><img url="<?php echo $GLOBALS['config']->baseUrl.$thumbnailImageUrl; ?>" title="<?php echo $taxaObservation->getData('title'); ?>"><?php endif;?> 
		        <?php echo $taxaObservation->getData('description'); ?>]]></description>
                <?php if (is_object($dateTime)) : 
                ?><pubDate><?php echo $dateTime->format(DateTime::RSS);?></pubDate><?php
                echo PHP_EOL; endif; ?>
                <guid><?php echo $GLOBALS['config']->baseUrl;?>observation.php?id=<?php echo $taxaObservation->getData('id');?></guid>

            </item>
<?php endforeach; ?>
        </channel>
</rss>
<?php exit();
