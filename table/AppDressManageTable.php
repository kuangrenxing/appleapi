<?php
Globals::requireClass('Table');

class AppDressManageTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'app_dress_manage'
	);
	
}

Config::extend('AppDressManageTable', 'Table');