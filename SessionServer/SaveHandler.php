<?PHP
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stephan Schmidt <schst@php.net>                              |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * Functions that may be used with session_set_save_handler()
 *
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Stephan Schmidt <schst@php.net>
 */

/**
 * uses the client implementation
 */
require_once 'HTTP/SessionServer/Client.php';

/**
 * open session
 *
 * @param   string      session save path, used for host and port
 * @param   string      session name
 * @return  boolean
 */
function HTTP_SessionServer_SaveHandler_open($save_path, $session_name)
{
    list($host, $port) = explode(':', $save_path);
    
    $GLOBALS['__sessionname'] = $session_name;
    $GLOBALS['__session'] = &new HTTP_SessionServer_Client($host, $port);
    return true;
}

/**
 * close the session
 *
 * @return  boolean
 */
function HTTP_SessionServer_SaveHandler_close()
{
    $GLOBALS['__session']->close();
    return true;
}

/**
 * read data from session server
 *
 * @param   string      session id
 * @return  string
 */
function HTTP_SessionServer_SaveHandler_read($id)
{
    $GLOBALS['__session']->open($id);
    return $GLOBALS['__session']->get($GLOBALS['__sessionName']);
}

/**
 * write data to session server
 *
 * @param   string      session id
 * @param   string      session data
 * @return  boolean
 */
function HTTP_SessionServer_SaveHandler_write($id, $sess_data)
{
    $GLOBALS['__session']->put($GLOBALS['__sessionName'], $sess_data);
    return true;
}

/**
 * destroy session data
 *
 * @param   string      session id
 * @return  boolean
 */
function HTTP_SessionServer_SaveHandler_destroy($id)
{
    $GLOBALS['__session']->open($id);
    $GLOBALS['__session']->destroy();
    return true;
}

/**
 * garbage collection
 *
 * Has to be done on server side
 *
 * @param   string
 * @return  boolean
 */
function HTTP_SessionServer_SaveHandler_gc($maxlifetime)
{
    return true;
}

// register save handler
session_set_save_handler('HTTP_SessionServer_SaveHandler_open', 'HTTP_SessionServer_SaveHandler_close', 'HTTP_SessionServer_SaveHandler_read', 'HTTP_SessionServer_SaveHandler_write', 'HTTP_SessionServer_SaveHandler_destroy', 'HTTP_SessionServer_SaveHandler_gc');
?>