<?PHP
/**
 * Example that show the use of the session save handler
 *
 * @category    HTTP
 * @package     HTTP_SessionServer
 * @subpackage  Examples
 * @author      Stephan Schmidt <schst@php.net>
 */
 
/**
 * load the save handler functions
 */
require_once 'HTTP/SessionServer/SaveHandler.php';

session_save_path('localhost:9090');
session_start();

echo 'Content of Session: <br />';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

$_SESSION['time'] = time();
$_SESSION['date'] = date('Y-m-d');

?>
<a href="<?PHP echo $_SERVER['PHP_SELF'] . '?' . SID; ?>">Next page</a>