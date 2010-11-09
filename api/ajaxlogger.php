<?php
/*
 * AJAXLogger
 * http://www.poweredbyjam.com/
 *
 * Copyright (c) 2009 Jacob Mather
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: 2009-08-01 10:18:21 -0400 (Mon, 1 Aug 2009)
 * Revision: 1
 */

// DB Config
define('AJAXLogger_DB_TYPE', 'mysql');
define('AJAXLogger_DB_HOST', 'localhost');
define('AJAXLogger_DB_PORT', '3306');
define('AJAXLogger_DB_USER', 'pg_adm');
define('AJAXLogger_DB_PASS', 'tmppass123');
define('AJAXLogger_DB_NAME', 'pg_db');
define('AJAXLogger_DB_DSN', '');
define('AJAXLogger_DB_PREFIX', 'AJAXLogger_');
define('AJAXLogger_DB_ACTIVITY_TIMEOUT', 60);

class AJAXLoggerResponse
{
	var $status;
	var $msgs;
	var $data;
	function AJAXLoggerResponse($status = false, $msgs = array(), $data = null)
	{
		$this->status = $status;
		if (!is_array($msgs))
		{
			$msgs = array($msgs);
		}
		$this->msgs = $msgs;
		$this->data = $data;
	}
	function setStatus($status)
	{
		if ($status)
		{
			$this->status = true;
		}
		$this->status = false;
	}
	function clearMsgs()
	{
		$this->msgs = array();
	}
	function addMsg($msg)
	{
		$this->msgs[] = $msg;
	}
	function hasMsg()
	{
		return (count($this->msgs) > 0)?true:false;
	}
	function getJSON()
	{
		$ret = array('status'=>$this->status,'msg'=>implode('<br />',$this->msgs), 'data'=>$this->data);
		return json_encode($ret);
	}
}
class AJAXLoggerWrapper
{
	var $obj;
	function __construct($obj)
	{
		$this->obj = $obj;
	}
	function __call($name, $params)
	{
		if (method_exists($this->obj, $name))
		{
			return call_user_func_array(array($this->holder, $name), $arguments);
		}
	}
	function __isset($name)
	{
		if (isset($this->obj->$name))
		{
			return true;
		}
		return false;
	}
	function __unset($name)
	{
		unset($this->obj->$name);
	}
	function __get($name)
	{
		return $this->obj->$name;
	}
	function __set($name, $value)
	{
		$this->obj->$name = $value;
	}
}
class AJAXLoggerWrapperPDO extends AJAXLoggerWrapper
{
	function prepare($statement, $driver_options = array())
	{
		$s = $obj->prepare($statement, $driver_options);
		return AJAXLoggerWrapperPDOStatement($s);
	}
}
class AJAXLoggerWrapperPDOStatement
{
	function execute($input_parameters = array())
	{
		if (!$this->obj->execute($input_parameters))
		{
			// log error
			return false;
		}
		return true;
	}
}
class AJAXLogger
{
	static $active = null;
	static $db = null;
	/**
	 * @return PDO
	 */
	static function getConnection()
	{
		if (self::$db === null)
		{
			self::$db = self::getNewConnection();
		}
		return self::$db;
	}
	/**
	 * @return PDO
	 */
	static function getNewConnection()
	{
		if (self::$db === null)
		{
			$dbtype = AJAXLogger_DB_TYPE;
			$dbhost = AJAXLogger_DB_HOST;
			$dbport = AJAXLogger_DB_PORT;
			$dbuser = AJAXLogger_DB_USER;
			$dbpassword = AJAXLogger_DB_PASS;
			$database = AJAXLogger_DB_NAME;
			$dsn = AJAXLogger_DB_DSN;
	//		try {
				if (strlen($dsn) > 2){
					return new PDO('odbc:DSN='.$dsn.';UID='.$dbuser.';PWD='.$dbpassword);
				} else {
					return new PDO($dbtype.':host='.$dbhost.';dbname='.$database, $dbuser, $dbpassword);
				}
	//		} catch (PDOException $e) {
	//			throw new Exception('AJAXLogger DB Access Failed.');
	//			return null;
	//		}
		}
	}
	static function getWrappedConnection()
	{
		$c = self::getConnection();
		return new AJAXLoggerWrapperPDO($c);
	}
	static function getNewSession()
	{
		$db = self::getConnection();
		$id = md5(time().'AJAXLogger'.rand(1,1000000));
		$SQL = 'INSERT INTO '.AJAXLogger_DB_PREFIX.'sessions (id, last_hit) VALUES (';
		$SQL .= $db->quote($id).','.time().')';
		if ($db->query($SQL))
		{
			return $id;
		} else {
			return false;
		}
	}
	static function cleanSessions()
	{
		$db = self::getConnection();
		$SQL = 'DELETE FROM '.AJAXLogger_DB_PREFIX.'sessions WHERE last_hit < ';
		$SQL .= (time() - AJAXLogger_DB_ACTIVITY_TIMEOUT);
		$res = $db->query($SQL);
		$res->closeCursor();
	}
	static function validateSession($id)
	{
		self::cleanSessions();
		$db = self::getConnection();
		$SQL = 'SELECT COUNT(*) as mcnt FROM '.AJAXLogger_DB_PREFIX.'sessions WHERE id = ';
		$SQL .= $db->quote($id);
		$res = $db->query($SQL);
		$row = $res->fetch(PDO::FETCH_ASSOC);
		if ($row['mcnt'] > 0)
		{
			$res->closeCursor();
			$SQL = 'UPDATE '.AJAXLogger_DB_PREFIX.'sessions SET last_hit = '.time().' WHERE ';
			$SQL .= 'id = '.$db->quote($id);
			$res = $db->query($SQL);
			$res->closeCursor();
			return true;
		}
		return false;
	}
	static function checkActivity()
	{
		if (self::$active !== null)
			return;
		self::cleanSessions();
		$db = self::getConnection();
		$SQL = 'SELECT COUNT(*) as mcnt FROM '.AJAXLogger_DB_PREFIX.'sessions WHERE last_hit >= ';
		$SQL .= (time() - AJAXLogger_DB_ACTIVITY_TIMEOUT);
		$r = $db->query($SQL);
		if ($r)
		{
			$row = $r->fetch(PDO::FETCH_ASSOC);
			$r->closeCursor();
			if ($row['mcnt'] > 0)
			{
				self::$active = true;
				return;
			}
		}
		self::$active = false;
	}
	static function logMessage($file, $function, $msg)
	{
		if (self::$active == null)
			self::checkActivity();
		if (!self::$active)
			return true;
		$db = self::getConnection();
		$SQL = 'INSERT INTO '.AJAXLogger_DB_PREFIX.'messages (ts, file, function, message)';
		$SQL .= ' VALUES ('.time().','.$db->quote($file).','.$db->quote($function);
		$SQL .= ','.$db->quote($msg).')';
		if ($db->query($SQL))
		{
			$r->closeCursor();
			return true;
		}
		return false;
	}
}