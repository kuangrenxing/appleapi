<?php
Globals::requireClass('Table');

class EverydayLogTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_everyday_log'
	);
}

Config::extend('EverydayLogTable', 'Table');