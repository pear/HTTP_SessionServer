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
 * Session storage base class
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Stephan Schmidt <schst@php.net>
 */

/**
 * open session for read access
 */
define('HTTP_SESSIONSERVER_STORAGE_MODE_READ',  'r');
 
/**
 * open session for write access
 */
define('HTTP_SESSIONSERVER_STORAGE_MODE_WRITE', 'w');

/**
 * open session for read and write access
 */
define('HTTP_SESSIONSERVER_STORAGE_MODE_READWRITE', 'rw');

/**
 * Session storage base class
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author  Stephan Schmidt <schst@php.net>
 */
class HTTP_SessionServer_Storage
{
   /**
    * options for the storage container
    *
    * @access   private
    * @var      array
    */
    var $_options = array();
    
   /**
    * current session id
    *
    * @access   private
    * @var      string
    */
    var $_sid  = null;
    
   /**
    * mode
    *
    * @access   private
    * @var      string
    */
    var $_mode = null;

   /**
    * data in the session
    *
    * @access   private
    * @var      array
    */
    var $_data = array();

   /**
    * constructor
    *
    * @access   public
    * @param    array
    */
    function HTTP_SessionServer_Storage($options = array())
    {
        $this->_options = array_merge($this->_options, $options);
    }
    
   /**
    * generate a new session id
    *
    * @access   public
    * @return   string
    */
    function generateNewId()
    {
        return md5(uniqid('session') . microtime());
    }
    
   /**
    * open an existing session
    *
    * @access   public
    * @param    string  session id
    * @param    integer mode  
    * @return   boolean
    */
    function open($sid, $mode = HTTP_SESSIONSERVER_STORAGE_MODE_READWRITE)
    {
        $this->_sid  = $sid;
        $this->_mode = $mode;
        return true;
    }

   /**
    * close the session
    *
    * @access   public
    * @return   boolean
    */
    function close()
    {
        $this->_sid  = null;
        $this->_data = array();
        $this->_mode = null;
        return true;
    }

   /**
    * commit the session
    *
    * @access   public
    * @return   boolean
    */
    function commit()
    {
        $this->_mode = 'r';
        return true;
    }

   /**
    * get a value from the session
    *
    * @access   public
    * @param    string  key
    * @return   mixed   value for the given key
    */
    function get($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return false;
    }

   /**
    * store a value in the session
    *
    * @access   public
    * @param    string      key
    * @param    mixed       value
    * @return   boolean
    */
    function put($key, $value)
    {
        if ($this->_mode == 'r') {
        	return false;
        }
        $this->_data[$key] = $value;
        return true;
    }

   /**
    * check, whether a key exists
    *
    * @access   public
    * @param    string      key
    * @return   boolean
    */
    function exists($key)
    {
        return isset($this->_data[$key]);
    }

   /**
    * remove a value from the session
    *
    * @access   public
    * @param    string      key
    * @return   boolean
    */
    function remove($key)
    {
        if ($this->_mode == 'r') {
        	return false;
        }

        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        return true;
    }

   /**
    * destroy the current session
    *
    * @access   public
    * @return   boolean
    */
    function destroy()
    {
        if ($this->_mode == 'r') {
        	return false;
        }
        $this->_sid  = null;
        $this->_data = array();
        $this->_mode = null;
        return true;
    }

   /**
    * chage the session id
    *
    * @access   public
    * @return   string
    */
    function regenerateId()
    {
        if ($this->_mode == 'r') {
        	return false;
        }
        $data  = $this->getAll();
        $mode  = $this->_mode;
        $this->destroy();
        $newId = $this->generateNewId();
        $this->open($newId, $mode);
        $this->setAll($data);
        return $newId;
    }
    
   /**
    * get all values in the session
    *
    * @access   public
    * @return   array
    */
    function getAll()
    {
        return $this->_data;
    }

   /**
    * set all values in the session
    *
    * @access   public
    * @param   array
    */
    function setAll($data)
    {
        if ($this->_mode == 'r') {
        	return false;
        }
        $this->_data = $data;
        return true;
    }

   /**
    * get all session keys
    *
    * @access   public
    * @return   array
    */
    function getKeys()
    {
        return array_keys($this->_data);
    }

   /**
    * check, whether the session is open
    *
    * @access   public
    * @return   array
    */
    function isOpen()
    {
        return !empty($this->_mode);
    }
}