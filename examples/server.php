<?PHP
/**
 * Example that show the use of the HTTP_SessionServer
 *
 * This example uses the filesystem backend.
 *
 * @category    HTTP
 * @package     HTTP_SessionServer
 * @subpackage  Examples
 * @author      Stephan Schmidt <schst@php.net>
 */

/**
 * needs the server
 */
require_once 'HTTP/SessionServer.php';

$options = array(
                    'save_path' => '/tmp'
                );

$server = &new HTTP_SessionServer('Filesystem', $options);
$server->service('localhost', 9090);
?>