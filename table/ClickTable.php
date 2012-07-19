<?php
Globals::requireClass('Table');

class ClickTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_click'
	);
}

Config::extend('ClickTable', 'Table');