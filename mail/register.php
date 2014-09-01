<p>
Ciao <?php echo $login ?>,<br/>
conferma il tuo utente cliccando su 
<a href="<?php echo $GLOBALS['config']->baseUrl; ?>user.php?confirmCode=<?php echo $user->getData('confirm_code')?>">
<?php echo $GLOBALS['config']->baseUrl; ?>?confirmCode=<?php echo $user->getData('confirm_code')?>
</a>
</p>
<p>Lo staff di <?php echo $GLOBALS['config']->siteName; ?></p>
