<?php
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
 * Client for the SessionServer
 *
 * Although this supports all needed functionality, you may
 * easily create your own client that integrates with your
 * session management
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Stephan Schmidt <schst@php.net>
 */

/**
 * open session for read access
 */
define('HTTP_SESSIONSERVER_CLIENT_MODE_READ',  'r');
 
/**
 * open session for write access
 */
define('HTTP_SESSIONSERVER_CLIENT_MODE_WRITE', 'w');

/**
 * open session for read and write access
 */
define('HTTP_SESSIONSERVER_CLIENT_MODE_READWRITE', 'rw');

/**
 * uses Net_Socket
 */
require_once 'Net/Socket.php';

/**
 * uses PEAR
 */
require_once 'PEAR.php';

/**
 * Example client for the SessionServer
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author  Stephan Schmidt <schst@php.net>
 */
class HTTP_SessionServer_Client extends PEAR
{
   /**
    * socket
    *
    * @access   private
    * @var      Net_Socket
    */
    var $_socket = null;
    
   /**
    * constructor
    *
    * @access   public
    * @param    string      host of the session  server
    * @param    int         port of the session server
    */
    function HTTP_SessionServer_Client($host, $port)
    {
        $this->_socket = &new Net_Socket();
        $this->_socket->connect($host, $port);
    }
    
   /**
    * destructor
    *
    * Disconnects the socket
    *
    * @access   public
    */
    function _HTTP_SessionServer_Client()
    {
        $this->_socket->disconnect();
    }
    
   /**
    * creates a new session
    *
    * @access   public
    * @return   string      new session id
    */
    function create()
    {
        return $this->_sendCommand('new');
    }
    
   /**
    * open an existing session
    *
    * @access   public
    * @param    string     session id
    */
    function open($sid, $mode = HTTP_SESSIONSERVER_CLIENT_MODE_READWRITE)
    {
        return $this->_sendCommand('open', $sid, $mode);
    }

   /**
    * get a session value
    *
    * @access   public
    * @param    string     key
    * @return   string     value
    */
    function get($key)
    {
        return $this->_sendCommand('get', $key);
    }

   /**
    * store a session value
    *
    * @access   public
    * @param    string     key
    * @param    string     value
    */
    function put($key, $value)
    {
        return $this->_sendCommand('put', $key, $value);
    }

   /**
    * check, whether a value exists
    *
    * @access   public
    * @param    string     key
    * @return   string     value
    */
    function exists($key)
    {
        return $this->_sendCommand('exists', $key);
    }

   /**
    * remove a session value
    *
    * @access   public
    * @param    string     key
    * @return   string     value
    */
    function remove($key)
    {
        return $this->_sendCommand('remove', $key);
    }

   /**
    * get all keys in the session
    *
    * @access   public
    */
    function getKeys()
    {
        $keys = $this->_sendCommand('keys');
        var_dump($keys);
        if (PEAR::isError($keys)) {
        	return $keys;
        }
        return explode('|', $keys);
    }

   /**
    * close an existing session
    *
    * @access   public
    */
    function close()
    {
        return $this->_sendCommand('close');
    }

   /**
    * commit the session (change mode to readonly)
    *
    * @access   public
    */
    function commit()
    {
        return $this->_sendCommand('commit');
    }

   /**
    * destroy an existing session
    *
    * @access   public
    */
    function destroy()
    {
        return $this->_sendCommand('destroy');
    }

   /**
    * generate a new session id while keeping the session data
    *
    * @access   public
    * @return   string      new session id
    */
    function regenerateId()
    {
        return $this->_sendCommand('regenerate_id');
    }

   /**
    * get all session data as an array
    *
    * @access   public
    * @return   array
    */
    function getAll()
    {
        $result = $this->_sendCommand('get_all');
        if (PEAR::isError($result)) {
        	return $result;
        }
        return unserialize($result);
    }
    
   /**
    * store all session data
    *
    * @access   public
    * @param   array
    */
    function putAll($data)
    {
        $data = serialize($data);
        return $this->_sendCommand('put_all', $data);
    }
    
   /**
    * utility function to send a command to the server
    *
    * @access   public
    * @param    string      command
    * @param    mixed       parameters (as many as the command requires)
    * @return   string      return value of the command
    */
    function _sendCommand($cmd)
    {
        $params = func_get_args();
        $data   = utf8_encode(implode(' ', $params));
        $this->_socket->writeLine($data);
        $result = trim($this->_socket->readLine());
        
        $result = explode(' ', $result, 2);
        if ($result[0] === 'err') {
        	return PEAR::raiseError($result[1]);
        }
        if (isset($result[1])) {
            return $result[1];
        }
        return true;
    }
}
?>