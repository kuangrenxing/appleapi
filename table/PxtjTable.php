<?php
Globals::requireClass('Table');

class PxtjTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pxtj'
	);
}

Config::extend('PxtjTable', 'Table');
