<?php
require __DIR__.'/../include/pageboot.php';

$coll = $GLOBALS['db']->query('SELECT `id`,`description` FROM `taxa` WHERE `description` != ""', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->toArray();
foreach($coll as $daxa) {
        $cleanText = html_entity_decode($daxa['description']);
        $cleanText = preg_replace('/^<p>/m','',$cleanText);
        $cleanText = preg_replace('/<\/p>$/m','',$cleanText);
        if ($daxa['description'] != $cleanText) {
            echo $cleanText.PHP_EOL.PHP_EOL;
            $GLOBALS['db']->query('UPDATE `taxa` SET `description`="'.addslashes($cleanText).'" WHERE `id` = '.$daxa['id'], \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
}

$coll = $GLOBALS['db']->query('SELECT `id`,`content` FROM `content` WHERE `content` != ""', \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)->toArray();
foreach($coll as $daxa) {
        $cleanText = html_entity_decode($daxa['content']);
        $cleanText = preg_replace('/^<p>/m','',$cleanText);
        $cleanText = preg_replace('/<\/p>$/m','',$cleanText);
        if ($daxa['content'] != $cleanText) {
            echo $cleanText.PHP_EOL.PHP_EOL;
            $GLOBALS['db']->query('UPDATE `content` SET `content`="'.addslashes($cleanText).'" WHERE `id` = '.$daxa['id'], \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
}

