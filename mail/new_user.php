<p>
Registrazione di un nuovo utente sul sito <?php echo $GLOBALS['config']->siteName; ?>
</p>
<ul>
    <li>Nome: <?php echo $profile->getData('first_name');?></li>
    <li>Cognome: <?php echo $profile->getData('last_name');?></li>
    <li>Email: <?php echo $profile->getData('email');?></li>
    <li>Indirizzo: <?php echo $profile->getData('address');?></li>
    <li>Città: <?php echo $profile->getData('city');?></li>
    <li>Provincia: <?php echo $profile->getData('province');?></li>
    <li>Stato: <?php echo $profile->getData('stato');?></li>
    <li>Telefono: <?php echo $profile->getData('phone');?></li>
</ul>
<p>Lo staff di <?php echo $GLOBALS['config']->siteName; ?></p>
