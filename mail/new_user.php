<?php if (!array_key_exists('profile', $GLOBALS) && isset($profile)) 
        $GLOBALS['profile']=$profile;
?>
<p>
Registrazione di un nuovo utente sul sito <?php echo $GLOBALS['config']->siteName; ?>
</p>
<ul>
    <li>Nome: <?php echo $GLOBALS['profile']->getData('first_name');?></li>
    <li>Cognome: <?php echo $GLOBALS['profile']->getData('last_name');?></li>
    <li>Email: <?php echo $GLOBALS['profile']->getData('email');?></li>
    <li>Indirizzo: <?php echo $GLOBALS['profile']->getData('address');?></li>
    <li>Città: <?php echo $GLOBALS['profile']->getData('city');?></li>
    <li>Provincia: <?php echo $GLOBALS['profile']->getData('province');?></li>
    <li>Stato: <?php echo $GLOBALS['profile']->getData('stato');?></li>
    <li>Telefono: <?php echo $GLOBALS['profile']->getData('phone');?></li>
</ul>
<p>Lo staff di <?php echo $GLOBALS['config']->siteName; ?></p>
