<?PHP
/**
 * Example that show the use of the HTTP_SessionServer_Client
 *
 * @category    HTTP
 * @package     HTTP_SessionServer
 * @subpackage  Examples
 * @author      Carsten Lucke <luckec@php.net>
 */

error_reporting(E_ALL);

/**
 * needs the server
 */
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