<?php $dicoItemColl = $taxa->getDicoItemColl();
$positions = array();
$lastPosition = 0;
foreach ($dicoItemColl->getItems() as $dicoItem): 
   $lastCharacter = substr($dicoItem->getData('id'),-1);
   if ($lastCharacter == 0) {
      $lastPosition++;
      $positions[substr($dicoItem->getData('id'),0,-1).'0']= $lastPosition;
      $positions[substr($dicoItem->getData('id'),0,-1).'1']= $lastPosition;
   }
   $label = $dicoItem->getData('text');
?>
<div>
   <?php echo str_repeat('&#160;', strlen($dicoItem->getData('id'))-1); ?>
   <?php echo $positions[$dicoItem->getData('id')]; ?>
    <span id="d<?php echo $dicoItem->getData('id'); ?>">
      <?php echo $label; ?><em><?php
      if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 0) : ?>
      <?php echo $dicoItem->getRawData('initials'); ?> <?php echo $dicoItem->getRawData('name'); ?>
      <?php elseif (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 1) : ?>
      <a href="<?php echo $GLOBALS['db']->config->baseUrl;?>?id=<?php echo $dicoItem->getData('taxa_id'); ?>"><?php echo $dicoItem->getRawData('initials'); ?> <?php echo $dicoItem->getRawData('name'); ?></a>
      <?php endif; ?>
      </em>
      <?php  if (is_numeric($dicoItem->getData('taxa_id')) && $GLOBALS['profile'] instanceof \login\user\Profile && $GLOBALS['profile']->getRole()->getData('id') >0 && $GLOBALS['profile']->getRole()->getData('id') <= 2 ) : ?>
      <a class="actions modify blank" title="Modifica" href="?task=dico&amp;action=edit&amp;id=<?php echo $dicoItem->getData('taxa_id');?>">Modifica</a>
      <?php endif; ?>
   </span>
</div>
<?php endforeach; 
