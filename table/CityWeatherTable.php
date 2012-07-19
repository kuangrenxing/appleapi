<?php
Globals::requireClass('Table');

class CityWeatherTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_city_weather'
	);
}

Config::extend('CityWeatherTable', 'Table');