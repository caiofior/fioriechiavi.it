<?php
echo exec('whoami').'<br>';
echo exec('php '.__DIR__.'/test.php 2>&1').PHP_EOL;