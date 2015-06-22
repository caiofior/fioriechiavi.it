<p>Messaggio dal sito <?php echo $GLOBALS['db']->config->siteName ?> da parte di <?php echo $_REQUEST['name'] ?><br/>
Mail: <a href="mailto:<?php echo $_REQUEST['mail']; ?>"><?php echo $_REQUEST['mail']; ?></a><br/>
Telefono: <?php echo $_REQUEST['phone']; ?><br/>
Fax: <?php echo $_REQUEST['fax']; ?><br/>
</p>
<p><?php echo $_REQUEST['message']; ?></p>
<p>Lo staff di <?php echo $GLOBALS['config']->siteName; ?></p>
