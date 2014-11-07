<?php
if (is_file(__DIR__.'/../include/zendRequire.php')) {
   $outputFileHandler =  fopen (__DIR__.'/../include/zendRequireCompiled.php','w');
   fwrite($outputFileHandler, '<?php');
   require __DIR__.'/../include/zendRequire.php';
   foreach (get_included_files() as $file) {
      if (
            basename($file) == 'compileZend.php' ||
            basename($file) == 'zendRequire.php'
         ) {
         continue;
      }
      $content = file_get_contents($file);
      $content = preg_replace('/^[ ]*\<\?php/m', '', $content);
      $content = preg_replace('/\?\>[ ]*$/m', '', $content);
      $content = str_replace('if (interface_exists(\'Zend\Loader\SplAutoloader\'))', 'if (false)', $content);
      
      
      fwrite($outputFileHandler, $content);
   }
}
fclose($outputFileHandler);
die();
if (is_file(__DIR__.'/../include/zendRequire.php')) {
   $inputFileHandler =  fopen (__DIR__.'/../include/zendRequire.php','r');
   
   while ($row = fgets ($inputFileHandler, 4096)) {
      if (preg_match('/^[ ]*require(_once)?(.*)/', $row, $matches)) {
         $fileNameToInclude  = eval('echo '.$matches[2]);
         var_dump($fileNameToInclude);
         /**if (is_file($fileNameToInclude)) {
            var_dump(file_get_contents($fileNameToInclude));
         }*/
      }
   }
}

