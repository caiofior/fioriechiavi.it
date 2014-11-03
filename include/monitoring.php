<?php
/**
* @author Claudio Fior <caiofior@gmail.com>
* @copyright CRA
* MOnitoring script
*/
//ini_set('display_errors', 0);
$script_start_time= microtime(true);
/**
* Resource usage monitoring
*/
function server_resource_monitoring() {
$error_codes = array(
1=>'E_ERROR',
2=>'E_WARNING',
4=>'E_PARSE',
8=>'E_NOTICE',
16=>'E_CORE_ERROR',
32=>'E_CORE_WARNING',
64=>'E_COMPILE_ERROR',
128=>'E_COMPILE_WARNING',
256=>'E_USER_ERROR',
512=>'E_USER_WARNING',
1024=>'E_USER_NOTICE',
2048=>'E_STRICT',
4096=>'E_RECOVERABLE_ERROR',
8192=>'E_DEPRECATED',
16384=>'E_USER_DEPRECATED',
32767=>'E_ALL'
);
$error = error_get_last();
$error_message = '';
if ($error['type'] != 2 &&
$error['type'] != 8 &&
$error['type'] != 32 &&
$error['type'] != 128 &&
$error['type'] != 512 &&
$error['type'] != 1024 &&
$error['type'] != 2048 &&
$error['type'] != 8192 &&
$error['type'] != 16384 &&
$error['type'] != '') {
$error["type"] = $error_codes[$error["type"]];
$error_message = "type\t" . $error["type"] . PHP_EOL;
$error_message .= "message\t" . $error["message"] . PHP_EOL;
$error_message .= "file\t" . $error["file"] . PHP_EOL;
$error_message .= "line\t" . $error["line"] . PHP_EOL;
if (key_exists('DEBUG_MAIL', $GLOBALS) && $GLOBALS['DEBUG_MAIL'] != '') {
$mail = new Zend_Mail('UTF-8');
$mail->setBodyText($error_message);
$mail->setFrom($GLOBALS['MAIL_ADMIN_CONFIG']['from'], $GLOBALS['MAIL_ADMIN_CONFIG']['from_name']);
$mail->addTo($GLOBALS['DEBUG_MAIL'], $GLOBALS['DEBUG_MAIL']);
$mail->setSubject('Error in ' . $_SERVER['SERVER_NAME']);
try {
$mail->send(new Zend_Mail_Transport_Smtp($GLOBALS['MAIL_ADMIN_CONFIG']['server'], $GLOBALS['MAIL_ADMIN_CONFIG']));
} catch (Exception $e) {}
}
if (key_exists('CACHE', $GLOBALS))
$GLOBALS['CACHE']->clean();
}
else
$error = '';
if (!(isset($PHPUNIT) && $PHPUNIT) && !headers_sent()) {
if (key_exists('firephp', $GLOBALS))
$GLOBALS['firephp']->error($error);
}
if (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log')) {
   mkdir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log');
}
if (class_exists('SQLite3'))
$resource_log_db = new SQLite3(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'script_performance.db');
else if (class_exists('PDO') && in_array ('sqlite',PDO::getAvailableDrivers()))
$resource_log_db = new PDO('sqlite:'.__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'script_performance.db');
if (isset($resource_log_db)) {
$resource_log_db->exec('CREATE TABLE IF NOT EXISTS resources (
url TEXT,
site TEXT,
ip TEXT,
user_agent TEXT,
datetime NUMERIC,
total_time NUMERIC,
execution_time NUMERIC,
memory NUMERIC,
error TEXT
);');
@$resource_log_db->exec('DELETE FROM resources WHERE datetime < DATETIME("now","-1 hour");');
@$resource_log_db->exec('INSERT INTO resources (
url,
site,
ip,
user_agent,
datetime,
total_time,
execution_time,
memory,
error
) VALUES (' .
'"' . addslashes('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING']) . '",' .
'"' . addslashes($_SERVER['SERVER_NAME']) . '",' .
'"' . addslashes($_SERVER['REMOTE_ADDR']) . '",' .
'"' . addslashes($_SERVER['HTTP_USER_AGENT']) . '",' .
'DATETIME("now"),' .
'"' . addslashes(microtime(true) - $_SERVER['REQUEST_TIME']) . '",' .
'"' . addslashes(microtime(true) - $GLOBALS['script_start_time']) . '",' .
'"' . addslashes(number_format(memory_get_peak_usage() / (1024 * 1024), 2)) . '",' .
'"' . htmlentities ($error_message) . '"' .
');');
if (rand(1, 100) == 100)
@$resource_log_db->exec('VACUUM;');
/*$handle = fopen(__DIR__.'/zendRequire.php','w');
fwrite($handle,'<?php '.PHP_EOL);
foreach (get_included_files() as $file) {
   if (preg_match('/\\/Zend\\//',$file)) {
      $file = preg_replace('/.*\\/Zend\\//', '__DIR__.\'/../lib/zendframework/library/Zend/', $file).'\'';
      fwrite($handle,'require '.$file.';'.PHP_EOL);
   }
}
fclose($handle);*/
}
}
register_shutdown_function('server_resource_monitoring');
