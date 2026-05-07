<?php

define('FG_DIR', '/var/www/html/');
require_once(FG_DIR . 'inc/config.php');

if (@$_REQUEST['key'] != FG_SECKEY && @$_COOKIE['home_key'] != FG_SECKEY) die();
setcookie('home_key', FG_SECKEY, time() + 10 * 366 * 24 * 3600, '/', '', false, true);

$dev = $DB->select("SELECT *, `name` AS ARRAY_KEY FROM `devices` WHERE 1 ORDER BY `id` ASC");
foreach ($dev as &$item) {
	$item['data'] = json_decode($item['data'], 1);
}

?>
<html>
<head>
<meta charset="UTF-8">
<title>Figaroo Home</title>
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="icon" href="./favicon.ico">
<link rel="apple-touch-icon-precomposed" href="./favicon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<link href="//fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
<link rel="stylesheet" href="./styles.<?=filemtime('./styles.css')?>.css" type="text/css">
<script src="./jquery.js" type="text/javascript"></script>
<script type="text/javascript">
var FG_SECKEY = '<?=FG_SECKEY?>',
	FG_CO2_LIM1 = <?=FG_CO2_LIM1?>,
	FG_CO2_LIM2 = <?=FG_CO2_LIM2?>;
</script>
</head>
<body>
<h1>Figaroo Home</h1>
<div id="panel">
	<div id="dev-klimat" class="block block-hvac1" data-id="<?=$dev['klimat']['id']?>" data-temp="<?=$dev['klimat']['data']['temp']?>" data-temp="<?=$dev['klimat']['data']['temp']?>">
		<div class="block-inner">
			<div class="block-updated"><?=writeTime($dev['klimat']['updated2'])?></div>
			<div class="hvac1-temp">
				<span class="hvac1-value hvac1-cold"><?=$dev['klimat']['data']['temp']?></span> <span class="hvac1-degrees">°C</span>
			</div>
			<div class="hvac1-curtemp">
				<span class="hvac1-curvalue"><?=$dev['klimat']['data']['curtemp']?></span> °C
			</div>
			<div class="block-title"><?=$dev['klimat']['title']?></div>
			<div class="hvac1-control hvac1-less">&ndash;</div>
			<div class="hvac1-control hvac1-more">+</div>
		</div>
	</div>
	<div id="dev-lustra" class="block block-switch1" data-id="<?=$dev['lustra']['id']?>" data-status="<?=$dev['lustra']['status']?>">
		<div class="block-inner switch1-button switch1-<?=$dev['lustra']['status']?>">
			<div class="block-updated"><?=writeTime($dev['lustra']['updated2'])?></div>
			<div class="block-title"><?=$dev['lustra']['title']?></div>
		</div>
	</div>
	<div id="dev-vnutri" class="block block-meteo1" data-id="<?=$dev['vnutri']['id']?>">
		<div class="block-inner meteo1-wrapper">
			<div class="block-updated"><?=writeTime($dev['vnutri']['updated2'])?></div>
			<div class="meteo1-block meteo1-temp">
				<div class="meteo1-data">
					<span class="meteo1-value"><?=$dev['vnutri']['data']['temp']?></span>
					<span class="meteo1-point">°C</span>
				</div>
				<div class="meteo1-title">Температура</div>
			</div>
			<div class="meteo1-block meteo1-humd">
				<div class="meteo1-data">
					<span class="meteo1-value"><?=$dev['vnutri']['data']['humd']?></span>
					<span class="meteo1-point">%</span>
				</div>
				<div class="meteo1-title">Влажность</div>
			</div>
			<div class="meteo1-block meteo1-co2">
				<div class="meteo1-data">
					<span class="meteo1-value <?=( $dev['vnutri']['data']['co2'] > FG_CO2_LIM2 ? 'meteo1-badval' : ( $dev['vnutri']['data']['co2'] > FG_CO2_LIM1 ? 'meteo1-midval' : 'meteo1-nrmval' ) )?>"><?=$dev['vnutri']['data']['co2']?></span>
					<span class="meteo1-point">ppm</span>
				</div>
				<div class="meteo1-title">Доля CO<sub>2</sub></div>
			</div>
			<div class="meteo1-block meteo1-press">
				<div class="meteo1-data">
					<span class="meteo1-value"><?=$dev['vnutri']['data']['press']?></span>
					<span class="meteo1-point">mm</span>
				</div>
				<div class="meteo1-title">Давление</div>
			</div>
			<div class="block-title"><?=$dev['vnutri']['title']?></div>
		</div>
	</div>
	<div id="dev-pogoda" class="block block-pogoda">
		<div class="block-inner pogoda-informer">
			<img class="pogoda-image" width="150" height="150" src="//info.weather.yandex.net/2/4_white.ru.png?domain=ru" alt="Погода">
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<div id="slogan">
	Science is magic
</div>
<div id="footer">
	&copy; Valery Kirkizh, 2017-<?=date('Y')?>
</div>
<script src="./realplexor.js" type="text/javascript"></script>
<script src="./scripts.<?=filemtime('./scripts.js')?>.js" type="text/javascript"></script>
</body>
</html>
