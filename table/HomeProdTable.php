<?php
Globals::requireClass('Table');

class HomeProdTable extends Table
{
	public static $defaultConfig = array(
		'database' => 'homepage',
		'table' => 'h415_prod'
	);
}

Config::extend('HomeProdTable', 'Table');