var realplexor = new Dklab_Realplexor('http://home.figaroo.ru:8088/');
console.log(realplexor);
realplexor.subscribe('home', function(res) {
	var name = res.name,
		type = res.type,
		block = $('#dev-' + name);
	switch (type) {
		case 'switch1':
			var status = res.status,
				button = block.find('.switch1-button');
			if (status == 'on') {
				button.addClass('switch1-on');
				button.removeClass('switch1-off');
			} else {
				button.addClass('switch1-off');
				button.removeClass('switch1-on');
			}
			block.data('status', status);
		break;
		case 'hvac1':
			var temp = res.temp,
				curtemp = res.curtemp;
			block.data('temp', temp);
			block.find('.hvac1-value').text(temp);
			block.data('curtemp', curtemp);
			block.find('.hvac1-curvalue').text(curtemp);
		break;
		case 'meteo1':
			var temp = res.temp,
				humd = res.humd,
				co2 = res.co2,
				press = res.press;
			block.find('.meteo1-temp .meteo1-value').text(temp);
			block.find('.meteo1-humd .meteo1-value').text(humd);
			block.find('.meteo1-co2 .meteo1-value').text(co2);
			co2 = parseInt(co2, 10);
			if (co2 > FG_CO2_LIM2) {
				block.find('.meteo1-co2 .meteo1-value').removeClass('meteo1-nrmval');
				block.find('.meteo1-co2 .meteo1-value').removeClass('meteo1-midval');
				block.find('.meteo1-co2 .meteo1-value').addClass('meteo1-badval');
			} else if (co2 > FG_CO2_LIM1) {
				block.find('.meteo1-co2 .meteo1-value').removeClass('meteo1-nrmval');
				block.find('.meteo1-co2 .meteo1-value').addClass('meteo1-midval');
				block.find('.meteo1-co2 .meteo1-value').removeClass('meteo1-badval');
			} else {
				block.find('.meteo1-co2 .meteo1-value').addClass('meteo1-nrmval');
				block.find('.meteo1-co2 .meteo1-value').removeClass('meteo1-midval');
				block.find('.meteo1-co2 .meteo1-value').removeClass('meteo1-badval');
			}
			block.find('.meteo1-press .meteo1-value').text(press);
		break;
	}
});
realplexor.execute();

$('#panel .block-switch1 .switch1-button').on('click', function(){
	var block = $(this).parents('.block'),
		id = block.data('id'),
		status = block.data('status');
	if (status == 'on') {
		status = 'off';
		$(this).addClass('switch1-off');
		$(this).removeClass('switch1-on');
	} else {
		status = 'on';
		$(this).addClass('switch1-on');
		$(this).removeClass('switch1-off');
	}
	block.data('status', status);
	$.get('./ajax.php', {
			'key': FG_SECKEY,
			'id': id,
			'status': status
		}, function (res) {},
		'json'
	);
});

$('#panel .block-hvac1 .hvac1-less').on('click', function(){
	var block = $(this).parents('.block'),
		id = block.data('id'),
		temp = parseInt(block.data('temp'), 10);
	if (temp <= 17) return;
	temp--;
	block.data('temp', temp);
	block.find('.hvac1-value').text(temp);
	$.get('./ajax.php', {
			'key': FG_SECKEY,
			'id': id,
			'temp': temp
		}, function (res) {},
		'json'
	);
});
$('#panel .block-hvac1 .hvac1-more').on('click', function(){
	var block = $(this).parents('.block'),
		id = block.data('id'),
		temp = parseInt(block.data('temp'), 10);
	if (temp >= 30) return;
	temp++;
	block.data('temp', temp);
	block.find('.hvac1-value').text(temp);
	$.get('./ajax.php', {
			'key': FG_SECKEY,
			'id': id,
			'temp': temp
		}, function (res) {},
		'json'
	);
});

setInterval(function(){
	$('#panel .pogoda .pogoda-image').attr('src', '//info.weather.yandex.net/2/4_white.ru.png?domain=ru&a=' + (new Date()).getTime());
}, 60000);
