<?php
if (key_exists('sEcho', $_REQUEST)) {
      $result = array();
      $taxaKindColl = new \flora\taxa\TaxaKindColl($GLOBALS['db']);
      $taxaKindColl->loadAll($_REQUEST);
      $result['sEcho']=intval($_REQUEST['sEcho']);
      $result['iTotalRecords']=$taxaKindColl->countAll();
      $result['iTotalDisplayRecords']=$taxaKindColl->count();
      $result['aaData']=array();
      $columns = $taxaKindColl->getColumns();
      foreach($taxaKindColl->getItems() as $key => $user) {
         $row=array();
         foreach($columns as $column) {
            $data = $user->getRawData($column);
            if ($column == 'active') {
               $checked='';
               if ($data ==1)
                  $checked='checked="checked" ';
               $data = '<input '.$checked.'type="checkbox" name="active">';
            } 
            $row[] = $data;     
         }
         $result['aaData'][]=$row;
      }
      header('Content-Type: application/json');
      echo json_encode($result);
      exit;
}
$this->getTemplate()->setBlock('middle','administrator/taxa_category/list.phtml');
$this->getTemplate()->setBlock('footer','administrator/taxa_category/footer.phtml');  