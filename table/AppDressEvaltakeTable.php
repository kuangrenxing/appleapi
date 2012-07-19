<?php
Globals::requireClass('Table');

class AppDressEvaltakeTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'app_dress_evaltake'
	);
}

Config::extend('AppDressEvaltakeTable', 'Table');