<?php

class Func {
	public static function debug() {
		$args = func_get_args();
		if (!count($args)) return;
		foreach ($args as $n => $var) {
			$var = print_r($var, true);
			echo defined('NOHTML') ? $var : '<pre>' . htmlspecialchars($var, ENT_QUOTES, 'UTF-8') . '</pre>';
			echo "\n\n";
		}
	}
	public static function ddebug() {
		call_user_func_array(array('Func', 'debug'), func_get_args());
		die();
	}
	public static function status($status) {
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status);
		header('Status: ' . $status);
	}
	public static function cache($time = 3600) {
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $time) . ' GMT');
		header('Cache-Control: max-age=' . $time . ', private');
	}
	public static function nocache() {
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
	}
	public static function modified($time = null) {
		if ($time === null) $time = time();
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $time) . ' GMT');
	}
	public static function redirect($url = '/', $code = 200) {
		if (!preg_match("#^[a-z]+://#uis", $url)) {
			$url = URL . preg_replace("/^\//uis", "", $url);
		}
		switch ($code) {
			case 301:
				self::status('301 Moved Permanently');
			break;
			case 302:
				self::status('302 Found');
			break;
			case 200:
			default:
				self::status('200 OK');
			break;
		}
		@header('Location: ' . $url);
		die('<html><head><meta name="refresh" content="0; url=' . $url . '"><script>location.href="' . $url . '";</script></head><body><a href="' . $url . '">' . $url . '</a></body></html>');
	}
	public static function mail($to, $subject = '', $message = '', $headers = array(), $type = 'plain') {
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

		if (!in_array($type, array('plain', 'html'))) return false;

		$headers = join("\r\n", array_merge(array(
			'MIME-Version: 1.0',
			'Content-type: text/' . $type . '; charset=UTF-8',
		), $headers));

		return mail($to, $subject, $message, $headers);
	}
	public static function recursive($func, $array) {
		if (!is_array($array)) return call_user_func($func, $array);
		foreach ($array as $k => $v) {
			if (is_array($v)) $array[$k] = self::recursive($func, $v);
			else $array[$k] = call_user_func($func, $v);
		}
		return $array;
	}
	public static function basicAuth($login, $pass, $title = 'Restricted Area') {
		if (!(@$_SERVER['PHP_AUTH_USER'] == $login && @$_SERVER['PHP_AUTH_PW'] == $pass)) {
			header('WWW-Authenticate: Basic realm="' . htmlsec($title) . '"');
			header('HTTP/1.1 401 Unauthorized');
			return false;
		}
		return true;
	}
}

function debug() {return call_user_func_array(array('Func', 'debug'), func_get_args());}
function ddebug() {return call_user_func_array(array('Func', 'ddebug'), func_get_args());}
function redirect() {return call_user_func_array(array('Func', 'redirect'), func_get_args());}

function writeTime($time) {
	return preg_replace("#^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$#uis", "$3.$2, $4:$5:$6", $time);
}
