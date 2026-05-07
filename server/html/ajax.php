<?php

define('NOHTML', true);
define('FG_DIR', '/var/www/html/');
require_once(FG_DIR . 'inc/config.php');

if (@$_REQUEST['key'] != FG_SECKEY && @$_COOKIE['home_key'] != FG_SECKEY) die();
setcookie('home_key', FG_SECKEY, time() + 10 * 366 * 24 * 3600, '/', 'home.figaroo.ru', false, true);

$realplexor = new Dklab_Realplexor('127.0.0.1', '10010');

$id = (int)@$_REQUEST['id'];
$item = $DB->selectRow("SELECT * FROM `devices` WHERE `id` = ?d LIMIT 1", $id);
if (!$item) die();
$item['data'] = json_decode($item['data'], 1);

switch ($item['type']) {

	case 'switch1':

		$status = (string)@$_REQUEST['status'];
		if (!in_array($status, array('on', 'off'))) die();
		$item['status'] = $status;

		$realplexor->send('home', array(
			'name' => $item['name'],
			'type' => $item['type'],
			'status' => $status,
		));

		$fp = fsockopen($item['addr'], 777, $errno, $errstr, 5);
		if ($fp) {
			fwrite($fp, '^#' . $item['uniq'] . '#' . FG_DEVPASS . '#status#' . $status. '#$');
			fclose($fp);
		}

	break;

	case 'hvac1':

		$temp = (int)@$_REQUEST['temp'];
		if ($temp < 17 || $temp > 30) die();
		$item['data']['temp'] = $temp;

		$realplexor->send('home', array(
			'name' => $item['name'],
			'type' => $item['type'],
			'temp' => $temp,
		));

		$fp = fsockopen($item['addr'], 777, $errno, $errstr, 5);
		if ($fp) {
			fwrite($fp, '^#' . $item['uniq'] . '#' . FG_DEVPASS . '#temp#' . $temp. '#$');
			fclose($fp);
		}

	break;

}

$item['data'] = json_encode($item['data']);
$DB->query("UPDATE `devices` SET `status` = ?, `data` = ?, `updated1` = NOW() WHERE `id` = ?d LIMIT 1", $item['status'], $item['data'], $id);
