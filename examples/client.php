<?PHP
/**
 * Example that show the use of the HTTP_SessionServer_Client
 *
 * @category    HTTP
 * @package     HTTP_SessionServer
 * @subpackage  Examples
 * @author      Stephan Schmidt <schst@php.net>
 */

/**
 * needs the client implementation
 */
require_once 'HTTP/SessionServer/Client.php';

$session = &new HTTP_SessionServer_Client('localhost', 9090);

$id = $session->create();
echo "created new session with $id.\n";

echo "storing new value in key time\n";
$session->put('time', time());
echo "storing new value in key foo\n";
$session->put('foo', 'bar');

echo "get all keys from the session\n";
$keys = $session->getKeys();
print_r($keys);

echo "closing session\n";
$session->close();

echo "\n";
echo "opening existing session with $id.\n";
$session2 = &new HTTP_SessionServer_Client('localhost', 9090);
$session2->open($id);

$time = $session2->get('time');
if (!PEAR::isError($time)) {
    echo "getting value from key time: $time\n";	
} else {
	echo $time->getMessage()."\n";
}

echo "removing value from session\n";
$session2->remove('time');

echo "get all keys from the session\n";
$keys = $session->getKeys();
print_r($keys);

$time = $session2->get('time');
if (!PEAR::isError($time)) {
    echo "getting value from key time: $time\n";	
} else {
	echo $time->getMessage()."\n";
}

echo "get all data from the session\n";
$all = $session2->getAll();
print_r($all);
$all['time'] = time();
$all['time_fmt'] = date('Y-m-d H:i:s');

echo "update all data in the session\n";
$session2->putAll($all);

echo "get all keys from the session\n";
$keys = $session->getKeys();
print_r($keys);

$session2->destroy();
?>