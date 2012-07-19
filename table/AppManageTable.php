<?php
Globals::requireClass('Table');

class AppManageTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_appmanage'
	);
}

Config::extend('AppManageTable', 'Table');