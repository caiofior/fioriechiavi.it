<?php
$dico = new \flora\dico\Dico($GLOBALS['db']);
if (array_key_exists('id', $_REQUEST)) {
   $dico->loadFromId($_REQUEST['id']);
} else {
   $dico->loadRoot();
}
$this->getTemplate()->setObjectData($dico);
$this->getTemplate()->setBlock('middle','general/index.phtml');

