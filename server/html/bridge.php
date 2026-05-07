<?php

define('NOHTML', true);
define('FG_DIR', '/var/www/html/');
require_once(FG_DIR . 'inc/config.php');

$result = array();

$realplexor = new Dklab_Realplexor('127.0.0.1', '10010');

$id = (int)@$_REQUEST['id'];
$item = $DB->selectRow("SELECT * FROM `devices` WHERE `id` = ?d LIMIT 1", $id);
if (!$item) die('{}');
$item['data'] = json_decode($item['data'], 1);

$cmd = (string)@$_REQUEST['cmd'];
$cmd = explode('/', $cmd);
array_shift($cmd);

$action = (string)@$cmd[0];

switch ($item['type']) {

	case 'meteo1':

		switch ($action) {

			case 'getCurrentTemperature':
				$result = array('value' => (float)$item['data']['temp']);
			break;

			case 'getCurrentRelativeHumidity':
				$result = array('value' => (int)$item['data']['humd']);
			break;

			case 'getCarbonDioxideDetected':
				$result = array('value' => (int)$item['data']['co2'] > FG_CO2_LIM2 ? 1 : 0);
			break;

		}

	break;

	case 'switch1':

		switch ($action) {

			case 'getOn':
				$result = array('value' => $item['status'] == 'on' ? 1 : 0);
			break;

			case 'setOn':
				$status = in_array(@$cmd[1], array('1', 'true')) ? 'on' : 'off';
				$item['status'] = $status;

				$DB->query("UPDATE `devices` SET `status` = ?, `updated1` = NOW() WHERE `id` = ?d LIMIT 1", $item['status'], $id);

				$realplexor->send('home', array(
					'name' => $item['name'],
					'type' => $item['type'],
					'status' => $status,
				));

				$fp = @fsockopen($item['addr'], 777, $errno, $errstr, 5);
				if ($fp) {
					fwrite($fp, '^#' . $item['uniq'] . '#' . FG_DEVPASS . '#status#' . $status. '#$');
					fclose($fp);
				}
			break;

		}

	break;

	case 'hvac1':

		switch ($action) {

			case 'getCurrentHeatingCoolingState':
				if ($item['status'] == 'on') {
					if ($item['data']['curtemp'] > $item['data']['temp'] - 1) {
						$result = array('value' => 2);
					} else {
						$result = array('value' => 0);
					}
				} else {
					$result = array('value' => 0);
				}
			break;

			case 'getTargetHeatingCoolingState':
				$result = array('value' => $item['status'] == 'on' ? 2 : 0);
			break;

			case 'getCurrentTemperature':
				$result = array('value' => (float)$item['data']['curtemp']);
			break;

			case 'getTargetTemperature':
				$result = array('value' => (int)$item['data']['temp']);
			break;

			case 'getTemperatureDisplayUnits':
				$result = array('value' => 0);
			break;

			case 'setTargetHeatingCoolingState':
				$status = (int)@$cmd[1] > 0 ? 'on' : 'off';
				$item['status'] = $status;

				sleep(2);

				$DB->query("UPDATE `devices` SET `status` = ?, `updated1` = NOW() WHERE `id` = ?d LIMIT 1", $item['status'], $id);

				$fp = @fsockopen($item['addr'], 777, $errno, $errstr, 5);
				if ($fp) {
					fwrite($fp, '^#' . $item['uniq'] . '#' . FG_DEVPASS . '#status#' . $status. '#$');
					fclose($fp);
				}
			break;

			case 'setTargetTemperature':
				$temp = (int)@$cmd[1];
				if ($temp < 17) $temp = 17;
				if ($temp > 30) $temp = 30;
				$item['data']['temp'] = $temp;

				$DB->query("UPDATE `devices` SET `data` = ?, `updated1` = NOW() WHERE `id` = ?d LIMIT 1", json_encode($item['data']), $id);

				$realplexor->send('home', array(
					'name' => $item['name'],
					'type' => $item['type'],
					'temp' => $temp,
				));

				$fp = @fsockopen($item['addr'], 777, $errno, $errstr, 5);
				if ($fp) {
					fwrite($fp, '^#' . $item['uniq'] . '#' . FG_DEVPASS . '#temp#' . $temp. '#$');
					fclose($fp);
				}
			break;

		}

	break;

}

die(json_encode($result));
