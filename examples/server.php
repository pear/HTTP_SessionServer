<?PHP
require_once 'HTTP/SessionServer.php';

$options = array(
                    'save_path' => '/tmp'
                );

$server = &new HTTP_SessionServer('Filesystem', $options);
$server->service('localhost', 9090);
?>