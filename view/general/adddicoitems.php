<?php
$addDicoColl = $taxa->getAddDicoColl();
if ($addDicoColl->count() >0) : ?>
<h4>Altre chiavi</h4>
<?php endif;
foreach($addDicoColl->getItems() as $addDico) :
    if ($addDico->getData('name') != '') : ?>
    <h5><?php echo $addDico->getData('name'); ?></h5>
    <?php endif;
    $dicoItemColl = $addDico->getDicoItemColl();
    $positions = array();
    $lastPosition = 0;
    if ($addDico->getData('is_list') == 1) :
       
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
         <?php 
         if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 1 && $GLOBALS['profile'] instanceof \login\user\Profile && $GLOBALS['profile']->getRole()->getData('id') >0 && $GLOBALS['profile']->getRole()->getData('id') <= 2 ) : ?>
         <a class="actions modify blank" title="Modifica" href="<?php echo $GLOBALS['db']->config->baseUrl;?>administrator.php?task=taxa&amp;action=edit&amp;id=<?php echo $dicoItem->getData('taxa_id');?>">Modifica</a>
         <?php endif; ?>
      </span>
   </div>
   <?php endforeach;
    else :  
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
      <a class="taxaPreview" href="<?php echo $GLOBALS['db']->config->baseUrl;?>?id=<?php echo $dicoItem->getData('taxa_id'); ?>"><?php echo $dicoItem->getRawData('initials'); ?> <?php echo $dicoItem->getRawData('name'); ?></a>
      <?php endif; ?>
      </em>
      <?php  if (is_numeric($dicoItem->getData('taxa_id')) && $dicoItem->getRawData('status') == 1 && $GLOBALS['profile'] instanceof \login\user\Profile && $GLOBALS['profile']->getRole()->getData('id') >0 && $GLOBALS['profile']->getRole()->getData('id') <= 2 ) : ?>
      <a class="actions modify blank" title="Modifica" href="<?php echo $GLOBALS['db']->config->baseUrl;?>administrator.php?task=taxa&amp;action=edit&amp;id=<?php echo $dicoItem->getData('taxa_id');?>">Modifica</a>
      <?php endif; ?>
   </span>
</div>
<?php endforeach;
      endif;
endforeach;

