<?php
Globals::requireClass('Table');

class AppVersionTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_app_version'
	);
}

Config::extend('AppVersionTable', 'Table');