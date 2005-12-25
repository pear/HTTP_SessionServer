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
// | Author: Carsten Lucke <luckec@php.net>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * Session storage for filesystem database.
 *
 * This storage-driver needs PEAR::DB to work.
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Carsten Lucke <luckec@php.net>
 */

/**
 * base class for storage container
 */
require_once 'HTTP/SessionServer/Storage.php';

/**
 * needs PEAR::DB
 */
require_once 'DB.php';

/**
 * Session storage for filesystem database.
 *
 * This storage-driver needs PEAR::DB to work.
 *
 * @category HTTP
 * @package  HTTP_SessionServer
 * @author   Carsten Lucke <luckec@php.net>
 */
class HTTP_SessionServer_Storage_DB extends HTTP_SessionServer_Storage 
{
   /**
    * options for the container
    *
    * @var  array
    */    
    var $_options = array(
                            'dsn' => null,
                            'table' => null,
                            'col_sid' => null,
                            'col_data' => null
                        );

   /**
    * DB object
    *
    * @var  object
    */    
    var $_dbc = null;

   /**
    * Opens an existing session or creates a new one.
    * 
    * The access-mode for the storage container is not important,
    * as the database will handle concurrent accesses.
    * 
    * @access   public
    * @param    string  session id
    * @param    integer mode  
    * @return   boolean
    */
    function open($sid, $mode = HTTP_SESSIONSERVER_STORAGE_MODE_READWRITE)
    {
        // check db-credentials
        if (is_null($this->_options['dsn']) || is_null($this->_options['table']) ||
                is_null($this->_options['col_sid']) || is_null($this->_options['col_data'])) {
            return false;
        }
        
        // establish db-connection
        $this->_dbc = &DB::connect($this->_options['dsn']);
        if (DB::isError($this->_dbc)) {
            return false;
        }
        
        // read session-data
        $qry    = sprintf('SELECT %s FROM %s WHERE %s = %s', 
                $this->_options['col_data'], $this->_options['table'], $this->_options['col_sid'], 
                $this->_dbc->quoteSmart($sid));
        $result = $this->_dbc->getRow($qry, array(), DB_FETCHMODE_ORDERED);
        if (DB::isError($result)) {
            return false;
        }
        
        if (! empty($result)) {
            $this->_data = unserialize($result[0]);
        } else {
            $this->_data = array();
        }
        
    	if (! is_array($this->_data)) {
    		$this->_data = array();
    	}
        
        return parent::open($sid, $mode);
    }
    
   /**
    * close the session
    *
    * @access   public
    * @return   boolean
    */
    function close()
    {
        if (! is_a($this->_dbc, 'DB_common')) {
            return false;
        }
        
        $insert = $this->_performInsert();
        if ($insert === true) {
            $qry = sprintf('UPDATE %s SET %s = %s WHERE %s = %s',
                    $this->_options['table'], $this->_options['col_data'], 
                    $this->_dbc->quoteSmart(serialize($this->_data)),
                    $this->_options['col_sid'], $this->_dbc->quoteSmart($this->_sid));
        } elseif($insert === false) {
            $qry = sprintf('INSERT INTO %s (%s, %s) VALUES(%s, %s)', 
                    $this->_options['table'], $this->_options['col_sid'], 
                    $this->_options['col_data'], $this->_dbc->quoteSmart($this->_sid), 
                    $this->_dbc->quoteSmart(serialize($this->_data)));
        } else {
            return false;
        }
        
        $result = $this->_dbc->query($qry);
        if (DB::isError($result)) {
            return false;
        }
        unset($this->_dbc);
        $this->_dbc = null;
        
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
        if (! is_a($this->_dbc, 'DB_common')) {
            return false;
        }
        
        $insert = $this->_performInsert();
        if ($insert === true) {
            $qry = sprintf('UPDATE %s SET %s = %s WHERE %s = %s',
                    $this->_options['table'], $this->_options['col_data'], 
                    $this->_dbc->quoteSmart(serialize($this->_data)),
                    $this->_options['col_sid'], $this->_dbc->quoteSmart($this->_sid));
        } elseif ($insert === false) {
            $qry = sprintf('INSERT INTO %s (%s, %s) VALUES(%s, %s)', 
                    $this->_options['table'], $this->_options['col_sid'], 
                    $this->_options['col_data'], $this->_dbc->quoteSmart($this->_sid), 
                    $this->_dbc->quoteSmart(serialize($this->_data)));
        } else {
            return false;
        }
        $result = $this->_dbc->query($qry);
        if (DB::isError($result)) {
            return false;
        }
        
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
        if (! is_a($this->_dbc, 'DB_common')) {
            return false;
        }
        
        $qry    = sprintf('DELETE FROM %s WHERE %s = %s',
                $this->_options['table'], $this->_options['col_sid'], 
                $this->_dbc->quoteSmart($this->_sid));
        $result = $this->_dbc->query($qry);
        if (DB::isError($result)) {
            return false;
        }
        unset($this->_dbc);
        $this->_dbc = null;
        
        return parent::destroy();
    }
    
    /**
     * Checks whether an UPDATE or INSERT has to be performed.
     * 
     * @access private
     * @return boolean
     * @throws object DB_Error
     */
    function _performInsert() {
        $qry = sprintf('SELECT COUNT(*) FROM %s WHERE %s = %s',
                $this->_options['table'], $this->_options['col_sid'], 
                $this->_dbc->quoteSmart($this->_sid));
        $result = $this->_dbc->getOne($qry);
        if (DB::isError($result)) {
            return $result;
        }
        
        return ($result > 0) ? true : false;
    }
}