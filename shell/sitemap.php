<?php
$handler = fopen(__DIR__.'/../sitemap.xml', 'w');
fwrite($handler,<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

EOT
);
require __DIR__.'/../include/pageboot.php';
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
   foreach($newTaxaColl->getItems() as $newTaxa) : 
   fwrite($handler,<<<EOT
    <url>
      <loc>{$GLOBALS['db']->config->baseUrl}?id={$newTaxa->getData('id')}</loc>

EOT
);
   if ($newTaxa->getData('change_datetime') != '' ):
   $data = substr($newTaxa->getData('change_datetime'),0,10);
   fwrite($handler,<<<EOT
      <lastmod>{$data}</lastmod>

EOT
);
    endif;
fwrite($handler,<<<EOT
    </url>

EOT
);
    endforeach;
endif;
fwrite($handler,<<<EOT
</urlset>
EOT
);
fclose($handler);
