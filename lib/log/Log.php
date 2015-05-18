<?php

namespace log;

/**
 * Taxa class
 *
 * @author caiofior
 */
class Log extends \Content {

    /**
     * Associates the database table
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db) {
        parent::__construct($db, 'log');
        if (array_key_exists('profile',$GLOBALS)) {
            $this->data['profile_id']=$GLOBALS['profile']->getData('id');
            $this->data['email']=$GLOBALS['profile']->getData('email');
        }
    }
    /**
     * Inserts a log with url and datetime
     * @param string $url
     */
    public function add($url,$action='',$label='') {
        $this->data['datetime']= date('Y-m-d H:i:s');
        $this->data['url']=$url;
        if ($action != '') {
            $this->data['action']=$action;
        }
        if ($label != '') {
            $this->data['label']=$label;
        }
        parent::insert();
    }
    
}
