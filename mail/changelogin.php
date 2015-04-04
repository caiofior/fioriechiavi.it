<p>
Ciao <?php echo $this->data['username'] ?>,<br/>
conferma il tuo utente cliccando su 
<a href="<?php echo $GLOBALS['config']->baseUrl; ?>user.php?changeLoginConfirmCode=<?php echo $this->getData('confirm_code')?>">
<?php echo $GLOBALS['config']->baseUrl; ?>user.php?changeLoginConfirmCode=<?php echo $this->getData('confirm_code')?>
</a>
</p>
<p>Lo staff di <?php echo $GLOBALS['config']->siteName; ?></p>
