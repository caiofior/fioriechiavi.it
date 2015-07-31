<?php
if (array_key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $logColl = new \log\LogColl($GLOBALS['db']);
      $logColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $request = $_REQUEST;
      unset($request['sSearch']);
      $result['iTotalRecords']=$logColl->countAll($request);
      $result['iTotalDisplayRecords']=$logColl->countAll($_REQUEST);
      $result['aaData']=array();
      $columns = $logColl->getColumns();
      foreach($logColl->getItems() as $key => $log) {
         $row=array();
         foreach($columns as $column) {
            $data = $log->getRawData($column);
	    if ($column == 'url' && $log->getRawData('label') != '') {
     	    	$row[] = '<a href="'.$data.'">'.$log->getRawData('label').'</a>';
	    } else {
		$row[] = $data;
	    }
            
         }
         $result['aaData'][]=$row;
      }
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
}
if (!array_key_exists('action',$_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
default:
   $this->getTemplate()->setBlock('middle','administrator/log/list.phtml');
   $this->getTemplate()->setBlock('footer','administrator/log/footer.phtml');  
break;
}