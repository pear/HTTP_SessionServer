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
 * Session storage for filesystem
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Stephan Schmidt <schst@php.net>
 */

require_once 'HTTP/SessionServer/Storage.php';

/**
 * Session storage for filesystem
 *98
 
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Stephan Schmidt <schst@php.net>
 */
class HTTP_SessionServer_Storage_Filesystem extends HTTP_SessionServer_Storage 
{
   /**
    * options for the container
    *
    * @var  array
    */    
    var $_options = array(
                            'save_path' => '/tmp'
                        );
    
   /**
    * filename of the session file
    *
    * @var  string
    */    
    var $_filename = null;

   /**
    * indicates whether the container has been opened
    *
    * @var  boolean
    */    
    var $_openend  = false;

   /**
    * file pointer
    *
    * @var  resource
    */    
    var $_fp = null;

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
        $this->_filename = $this->_options['save_path'] . '/' . $sid . '.txt';
        
        switch ($mode) {
            case HTTP_SESSIONSERVER_STORAGE_MODE_READ:
                if (file_exists($this->_filename)) {
            	   $this->_data = unserialize(file_get_contents($this->_filename));
                } else {
                    return false;
                }
                break;
            default:
                if (file_exists($this->_filename)) {
                    $this->_fp = fopen($this->_filename, 'r+');
                } else {
                    $this->_fp = fopen($this->_filename, 'w+');
                }
                flock($this->_fp, LOCK_EX);
                clearstatcache();
                if (filesize($this->_filename)>0) {
                    $tmp = fread($this->_fp, filesize($this->_filename));
                    $this->_data = unserialize($tmp);
                } else {
                    $this->_data = array();
                }
                break;
        }
        
    	if (!is_array($this->_data)) {
    		$this->_data = array();
    	}
        parent::open($sid, $mode);

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
        if (!is_resource($this->_fp)) {
            return parent::close();
        }
        fseek($this->_fp, 0);
        fputs($this->_fp, serialize($this->_data));
        flock($this->_fp, LOCK_UN);
        fclose($this->_fp);
        $this->_fp = null;
        return parent::close();
    }

   /**
    * commit the session
    *
    * @access   public
    * @return   boolean
    */
    function commit()
    {
        if (!is_resource($this->_fp)) {
            return false;
        }
        fseek($this->_fp, 0);
        fputs($this->_fp, serialize($this->_data));
        flock($this->_fp, LOCK_UN);
        fclose($this->_fp);
        $this->_fp = null;
        return parent::commit();
    }

   /**
    * destroy the current session
    *
    * @access   public
    * @return   boolean
    */
    function destroy()
    {
        if (is_resource($this->_fp)) {
            fclose($this->_fp);
        }
        @unlink($this->_filename);
        return parent::destroy();
    }
}