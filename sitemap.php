<?php
header ('Content-Type:text/xml');
echo <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
EOT;
require __DIR__.'/include/pageboot.php';
$newTaxaColl  = new \flora\taxa\TaxaColl($GLOBALS['db']);
$newTaxaColl->loadAll(array(
    'iDisplayStart'=>0,
    'iDisplayLength'=>200,
    'sColumns'=>'change_datetime',
    'iSortingCols'=>'1',
    'iSortCol_0'=>'0',
    'sSortDir_0'=>'DESC'
));
if($newTaxaColl->count() > 0):
   foreach($newTaxaColl->getItems() as $newTaxa) : ?>
    <url>
    <loc><?php echo $GLOBALS['db']->config->baseUrl?>?id=<?php echo $newTaxa->getData('id'); ?></loc>
    <?php if ($newTaxa->getData('change_datetime') != '' ): ?>
    <lastmod><?php echo substr($newTaxa->getData('change_datetime'),0,10); ?></lastmod>
    <?php endif; ?>
    </url>
<?php endforeach;
endif;
echo <<<EOT
</urlset>
EOT;
