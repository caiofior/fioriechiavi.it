<?php
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
case "data":
  $logName = __DIR__.'/../../log';
  if(!is_dir($logName)) {
     mkdir($logName);
  }
  $logName .= '/'.date('Y-m').'.csv';
  echo shell_exec('head -n 1 '.$logName);
  echo shell_exec('tail '.$logName);
  exit();
break;
default:
   $this->getTemplate()->setBlock('middle','administrator/access/main.phtml');
   $this->getTemplate()->setBlock('footer','administrator/access/footer.phtml');
break;
}
