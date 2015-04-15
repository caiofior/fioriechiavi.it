<?php
if (!array_key_exists('action', $_REQUEST)) {
   $_REQUEST['action']=null;
}
switch ($_REQUEST['action']) {
    case 'login':
        if (!array_key_exists('status', $_REQUEST)) {
            $_REQUEST['status']=null;
        }
        $m = new stdClass();
        switch ($_REQUEST['status'])  {
            case 'connected':
                $m->status = true;
                try{
                    $fb = \login\user\FacebookInstantiator::createLoginInstance($GLOBALS['db'], $_REQUEST['authResponse'], '');
                } catch (\Exception $e) {
                    if ($e->getCode() == 1504151435) {
                        $m->status = false;
                        $m->message = 'Accesso disabilitato dallo staff di '.$GLOBALS['config']->siteName;
                        echo json_encode($m);
                        exit;
                    } else {
                        throw $e;
                    }
                }
                $facebookSession = new \Zend\Session\Container('facebook_id');
                $facebookSession->facebook_id=$fb->getData('userID');
                pclose(popen('php ' . $GLOBALS['db']->baseDir . 'shell/facebook.php  '.$fb->getData('userID').' > /dev/null &', 'r'));
            break;
            case 'not_authorized':
                $m->status = false;
                $m->message = 'Autorizza la Facebook a fornire le credenziali di accesso a <a href="'.$GLOBALS['config']->baseUrl.'">'.$GLOBALS['config']->siteName.'</a>';
            break;
            default:
                $m->status = false;
                $m->message = 'Accedi a Facebook per fornire le credenziali di accesso a <a href="'.$GLOBALS['config']->baseUrl.'">'.$GLOBALS['config']->siteName.'</a>';
            break;
        }
        echo json_encode($m);
        exit;
    break;
}
