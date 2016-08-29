<?php $dicoItemColl = $taxa->getDicoItemColl();
$positions = array();
foreach ($dicoItemColl->getItems() as $pos =>$dicoItem): ?>
<div>
   <?php echo $pos+1; ?>
    <span id="d<?php echo $dicoItem->getData('id'); ?>">
      <?php echo $dicoItem->getData('text'); ?><em><?php
      if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 0) : ?>
      <?php echo $dicoItem->getRawData('initials'); ?> <?php echo $dicoItem->getRawData('name'); ?>
      <?php elseif (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 1) : ?>
      <a class="taxaPreview" href="<?php echo $GLOBALS['db']->config->baseUrl;?>?id=<?php echo $dicoItem->getData('taxa_id'); ?>"><?php echo $dicoItem->getRawData('initials'); ?> <?php echo $dicoItem->getRawData('name'); ?></a>
      <?php endif; ?>
      </em>
      <?php $photoUrl = $dicoItem->getPhotoUrl();
      if ($photoUrl !== false) :?>
      <img src="<?php echo $GLOBALS['db']->config->baseUrl.$photoUrl; ?>">
      <?php endif;
      if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 1 && $GLOBALS['profile'] instanceof \login\user\Profile && $GLOBALS['profile']->getRole()->getData('id') >0 && $GLOBALS['profile']->getRole()->getData('id') <= 2 ) : ?>
      <a class="actions modify blank" title="Modifica" href="<?php echo $GLOBALS['db']->config->baseUrl;?>administrator.php?task=taxa&amp;action=edit&amp;id=<?php echo $dicoItem->getData('taxa_id');?>">Modifica</a>
      <?php endif; ?>
   </span>
</div>
<?php endforeach;