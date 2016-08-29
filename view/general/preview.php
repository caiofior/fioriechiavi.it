<div>
<?php echo $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name'); ?>
</div>
<?php $imageColl = $taxa->getTaxaImageColl();
   if ($imageColl->count() > 0) : 
     $image = $imageColl->getFirst(); ?>
   <div>
         <img src="<?php echo $GLOBALS['db']->config->staticUrl.$image->getUrl();?>" alt="<?php echo $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name'); ?>"/>
   </div>      
   <?php  endif; ?>
<div>
<?php echo $taxa->getData('description'); ?>
</div>

