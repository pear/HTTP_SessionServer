<?PHP
error_reporting(E_ALL);

require_once 'HTTP/SessionServer.php';

$options = array(
                    'dsn' => 'mysql://root:offline@localhost/pear_http_sessionserver',
                    'table' => 'data',
                    'col_sid' => 'sid',
                    'col_data' => 'data',
                );

$server = &new HTTP_SessionServer('DB', $options);
$server->service('localhost', 9090);
?>