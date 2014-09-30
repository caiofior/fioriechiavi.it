<?php
$taxa = new \flora\taxa\Taxa($GLOBALS['db']);
$dico = new \flora\dico\Dico($GLOBALS['db']);
if (array_key_exists('id', $_REQUEST)) {
   $taxa->loadFromId($_REQUEST['id']);
   $dico = $taxa->getDico();
} else {
   $dico->loadRoot();
}
$this->getTemplate()->setObjectData(array('dico'=>$dico,'taxa'=>$taxa));
$this->getTemplate()->setBlock('middle','general/index.phtml');

