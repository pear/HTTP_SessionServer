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
 * Session Server class
 *
 * This is a simple server, that stores session data.
 *
 * It helps you doing several things:
 * - share session data among different servers
 * - share session data among different applications
 * - share session data among different programming languages
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Stephan Schmidt <schst@php.net>
 */

/**
 * uses Net_Server
 */ 
require_once 'Net/Server.php';

/**
 * Session Server class
 *
 * This is a simple server, that stores session data.
 *
 * It helps you doing several things:
 * - share session data among different servers
 * - share session data among different applications
 * - share session data among different programming languages
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author  Stephan Schmidt <schst@php.net>
 */
class HTTP_SessionServer
{
   /**
    * storage options
    *
    * @access   private
    * @var      array
    */
    var $_storage = array();
    
    /**
    * storage container
    *
    * @access   private
    * @var      HTTP_SessionServer_Storage
    */
    var $_store = null;
    
   /**
    * server
    *
    * @access   private
    * @var      Net_Server_Driver
    */
    var $_server = null;

   /**
    * commands that need an open session
    *
    * @access   private
    * @var      array
    */
    var $_requireOpenSession = array( 'get', 'put', 'close', 'commit', 'get_all', 'put_all', 'destroy', 'regenerate_id' );
    
   /**
    * constructor
    *
    * @access   public
    * @param    
    * @param    string      storage container
    * @param    array       parameters for the storage container
    */
    function HTTP_SessionServer($storage, $options = array())
    {
        $this->_storage = array($storage, $options);
    }
    
   /**
    * start the server
    *
    * @access   public
    */
    function service($host, $port)
    {
        list($storage, $options) = $this->_storage;
        // storage object
        $storageClass = 'HTTP_SessionServer_Storage_'.$storage;
        include_once 'HTTP/SessionServer/Storage/'. $storage . '.php';
        if (!class_exists($storageClass)) {
        	return PEAR::raiseError('Unknown storage container.');
        }
        $this->_store = &new $storageClass($options);
        
        // server object
        $this->_server = &Net_Server::create('fork', $host, $port);
        if (PEAR::isError($this->_server)) {
            return $this->_server;
        }
        $this->_server->setCallbackObject($this);
        
        $this->_server->start();
    }
    
   /**
    * recieve data from the client
    *
    * @access   public
    * @param    int     client id
    * @param    string  data
    * @return   boolean
    */
    function onReceiveData($clientId, $data)
    {
        $args = $this->_parseCommand($data);
        $command = array_shift($args);
        
        if ($this->_store->isOpen() || !in_array($command, $this->_requireOpenSession)) {
            $function = '_cmd' . ucfirst($command);
            if (is_callable(array($this, $function))) {
            	$result = call_user_func_array(array(&$this,$function), $args);
            } else {
            	$result = array('err', 'unknown command');
            }
        } else {
        	$result = array('err', 'No session opened.');
        }
        
        $result = utf8_encode(implode(' ', $result)) . "\r\n";
        $this->_server->sendData($clientId, $result);
        return true;
    }

   /**
    * create a new session
    *
    * This will automatically open the session.
    *
    * @access   private
    * @return   array
    */
    function _cmdNew()
    {
        $newId = $this->_store->generateNewId();
        $this->_store->open($newId);
        return array( 'ok', $newId);
    }

   /**
    * open an existing session
    *
    * @access   private
    * @param    string  session id
    * @return   array
    */
    function _cmdOpen($sid, $mode = 'rw')
    {
        $result = $this->_store->open($sid, $mode);
        if ($result === true) {
            return array('ok');
        }
        return array('err', 'Could not open session.');
    }
    
   /**
    * store a value in the session
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdPut($key, $value = '')
    {
        $result = $this->_store->put($key, $value);
        if ($result === true) {
        	return array('ok');
        }
        return array('err', 'Could not store value');
    }

   /**
    * get a value from the session
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdGet($key)
    {
        if (!$this->_store->exists($key)) {
        	return array('err', 'Key does not exist');
        }
        $value = $this->_store->get($key);
       	return array('ok', $value);
    }

   /**
    * check, whether a key exists
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdExists($key)
    {
        if (!$this->_store->exists($key)) {
        	return array('err', 'Key does not exist');
        }
       	return array('ok');
    }

   /**
    * remove a value
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdRemove($key)
    {
        if (!$this->_store->exists($key)) {
        	return array('err', 'Key does not exist');
        }
        $this->_store->remove($key);
       	return array('ok');
    }

   /**
    * destroy the session
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdDestroy()
    {
        $result = $this->_store->destroy();
        if ($result === true) {
        	return array('ok');
        }
        return array('err', 'Could not destroy session.');
    }

   /**
    * destroy the session
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdKeys()
    {
        $keys = $this->_store->getKeys();
        if (is_array($keys)) {
        	return array('ok', implode('|', $keys));
        }
        return array('err', 'Could not retrieve keys.');
    }

   /**
    * close the current session
    *
    * @access   private
    * @return   array
    */
    function _cmdClose()
    {
        $result = $this->_store->close();
        if ($result === true) {
        	return array('ok');
        }
        return array('err', 'Could not close session');
    }

   /**
    * regenerate id
    *
    * @access   private
    * @return   array
    */
    function _cmdRegenerate_id()
    {
        $newId = $this->_store->regenerateId();
        if ($newId === false) {
            return array('err', 'Could not regenerate session id.');
        }
        return array('ok', $newId);
    }

   /**
    * commit session (save data and change to readonly)
    *
    * @access   private
    * @return   array
    */
    function _cmdCommit()
    {
        $result = $this->_store->commit();
        if ($result === false) {
            return array('err', 'Could not commit session.');
        }
        return array('ok');
    }

   /**
    * get all values in the session
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdGet_all()
    {
        $result = $this->_store->getAll();
        if (is_array($result)) {
        	return array('ok', serialize($result));
        }
        return array('err', 'Could not get session data.');
    }

   /**
    * store all values in the session
    *
    * @access   private
    * @param    string  key
    * @param    string  value
    * @return   array
    */
    function _cmdPut_all($data)
    {
        $args = func_get_args();
        $data = implode(' ', $args);
        $data = unserialize(trim($data));
        $this->_store->setAll($data);
       	return array('ok');
    }

   /**
    * parse a command
    *
    * @access   private
    * @param    string      data received from server
    * @return   array
    */
    function _parseCommand($data)
    {
        $command = utf8_decode(trim($data));
        $command = explode(' ', $command, 3);
        return $command;
    }
}
?>