<?php
Globals::requireClass('Table');

class AppDressrecommTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'app_dress_recomm'
	);
}

Config::extend('AppDressrecommTable', 'Table');