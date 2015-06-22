<p>
Nuova segnalazione sul sito <?php echo $GLOBALS['config']->siteName; ?>
</p>
<ul>
    <li>Email: <?php echo $GLOBALS['profile']->getData('email');?></li>
    <li>Specie: <?php echo $taxa->getRawData('taxa_kind_initials').' '.$taxa->getData('name');?></li>
    <li>Titolo: <?php echo $taxaObservation->getData('title');?></li>
    <li>Descrizione: <?php echo $taxaObservation->getData('description');?></li>
    <li>Latitudine: <?php echo $taxaObservation->getData('latitude');?></li>
    <li>Longitudine: <?php echo $taxaObservation->getData('longitude');?></li>  
</ul>
<p>Lo staff di <?php echo $GLOBALS['config']->siteName; ?></p>
