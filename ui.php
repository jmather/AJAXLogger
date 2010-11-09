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

// CONFIG Options
$path_to_api = dirname(__FILE__).'/api';
$ui_password = '123';

function gR($name, $default = null)
{
	if (isset($_REQUEST[$name]))
		return $_REQUEST[$name];
	return $default;
}

require_once $path_to_api.'/ajaxlogger.php';

if (!function_exists('json_encode'))
{
	$r = new AJAXLoggerResponse(false, 'PHP5 Is Required.');
	echo $r->getJSON();
	exit(0);
}

try {
	$c = AJAXLogger::getConnection();
} catch (Exception $e)
{
	$r = new AJAXLoggerResponse(false, $e->getMessage());
	echo $r->getJSON();
	exit(0);
}

$task = (isset($_REQUEST['task']))?$_REQUEST['task']:'';
if ($task == 'login')
{
	if (gR('password') == $ui_password)
	{
		$id = AJAXLogger::getNewSession();
		if ($id == false)
		{
			$r = new AJAXLoggerResponse(false, 'We have hit a DB issue.');
			echo $r->getJSON();
			exit(0);
		} else {
			$r = new AJAXLoggerResponse(true, 'Login Successful', array('id'=>$id));
			error_log('Success: '.$r->getJSON());
			echo $r->getJSON();
			exit(0);
		}
	} else {
		$r = new AJAXLoggerResponse(false, 'Password is incorrect.');
		echo $r->getJSON();
		exit(0);
	}
}
if ($task == 'test')
{
	$words = (isset($_REQUEST['text']))?$_REQUEST['text']:'This is a test';
	AJAXLogger::logMessage('index.html', 'markTestEntry', $words);
	exit(0);
}
$session_id = gR('id');
if (!AJAXLogger::validateSession($session_id))
{
	$r = new AJAXLoggerResponse(false, 'Your session has expired.');
	echo $r->getJSON();
	exit(0);
}
$db = AJAXLogger::getConnection();
error_log(print_r($_REQUEST, true));
switch($task)
{
	case 'getList':
		$last_update = gR('last_update', 0);
		$last_idx = gR('last_id', 1);
		$file_filter = gR('file', null);
		$function_filter = gR('function', null);
		$SQL = 'SELECT * FROM '.AJAXLogger_DB_PREFIX.'messages WHERE ';
		$SQL .= '((ts = '.itnval($last_update).' AND id > '.intval($last_idx).')';
		$SQL .= ' OR ts > '.intval($last_update).')';
		if ($file_filter)
		{
			$SQL .= ' AND file = '.$db->quote($file_filter);
		}
		if ($function_filter)
		{
			$SQL .= ' AND function = '.$db->quote($function_filter);
		}

		$SQL .= ' ORDER BY ts DESC, id DESC';
		$SQL .= ' LIMIT 100';
		error_log($SQL);
		$res = $db->query($SQL);
		$data = array();
		$last_ts = 0;
		$last_id = 0;
		while($row = $res->fetch(PDO::FETCH_ASSOC))
		{
			$data[] = $row;
			if ($row['ts'] > $last_ts)
			{
				$last_ts = $row['ts'];
			}
			$last_id = $row['id'];
		}
		$d = array('data'=>$data,'time'=>time(), 'id'=> $last_id);

		$r = new AJAXLoggerResponse(true, 'getList', $d);
		echo $r->getJSON();
		break;
	case 'autocomplete_file':
		$db = AJAXLogger::getConnection();
		$f = $_GET['q'];
		$SQL = 'SELECT * FROM '.AJAXLogger_DB_PREFIX.'messages WHERE ';
		$SQL .= 'LEFT(file,'.strlen($f).') = '.$db->quote($f);
		$SQL .= ' GROUP BY file';
		$SQL .= ' LIMIT '.$_GET['limit'];
		$res = $db->query($SQL);
		$rows = array();
		while($row = $res->fetch(PDO::FETCH_ASSOC))
		{
			echo $row['file'].'|'.$row['file']."\n";
		}
		break;
	case 'autocomplete_function':
		$db = AJAXLogger::getConnection();
		$f = $_GET['q'];
		$SQL = 'SELECT * FROM '.AJAXLogger_DB_PREFIX.'messages WHERE ';
		$SQL .= 'LEFT(function,'.strlen($f).') = '.$db->quote($f);
		$SQL .= ' GROUP BY function';
		$SQL .= ' LIMIT '.$_GET['limit'];
		$res = $db->query($SQL);
		$rows = array();
		while($row = $res->fetch(PDO::FETCH_ASSOC))
		{
			echo $row['function'].'|'.$row['function']."\n";
		}
		break;
	default:
		$r = new AJAXLoggerResponse(false, 'Unknown command: '.$task);
		echo $r->getJSON();
		break;
}