<?php

define('NOHTML', true);
define('FG_DIR', '/var/www/html/');
require_once(FG_DIR . 'inc/config.php');

if (!$argv) die();

$ch1 = (string)@$argv[1];
$ch2 = (string)@$argv[6];
if ($ch1 != '^' || $ch2 != '$') die();

$id = (string)@$argv[2];
$pw = (string)@$argv[3];
$cmd = (string)@$argv[4];
$prm = (string)@$argv[5];

if ($pw != FG_DEVPASS) die();

$realplexor = new Dklab_Realplexor('127.0.0.1', '10010');

$item = $DB->selectRow("SELECT * FROM `devices` WHERE `id` = ?d LIMIT 1", $id);
if (!$item) die();
$item['data'] = json_decode($item['data'], 1);

switch ($item['type']) {

	case 'switch1':

		switch ($cmd) {

			case 'status':

				$status = $prm;
				if (!in_array($status, array('on', 'off'))) die();
				$item['status'] = $status;

				$realplexor->send('home', array(
					'name' => $item['name'],
					'type' => $item['type'],
					'status' => $status,
				));

			break;

		}

	break;

	case 'meteo1':

		switch ($cmd) {

			case 'update':

				list($temp, $humd, $co2, $press) = explode(',', $prm);
				$temp = sprintf("%.1f", (float)$temp);
				$humd = sprintf("%d", (int)$humd);
				$co2 = sprintf("%d", (int)$co2);
				$press = sprintf("%d", (int)$press);

				$item['data']['temp'] = $temp;
				$item['data']['humd'] = $humd;
				$item['data']['co2'] = $co2;
				$item['data']['press'] = $press;

				$realplexor->send('home', array(
					'name' => $item['name'],
					'type' => $item['type'],
					'temp' => $temp,
					'humd' => $humd,
					'co2' => $co2,
					'press' => $press,
				));

			break;

		}

	break;

	case 'hvac1':

		switch ($cmd) {

			case 'update':

				$curtemp = $prm;
				$item['data']['curtemp'] = $curtemp;

				$realplexor->send('home', array(
					'name' => $item['name'],
					'type' => $item['type'],
					'curtemp' => $curtemp,
				));

			break;

		}

	break;

}

$item['data'] = json_encode($item['data']);
$DB->query("UPDATE `devices` SET `status` = ?, `data` = ?, `updated2` = NOW() WHERE `id` = ?d LIMIT 1", $item['status'], $item['data'], $id);
