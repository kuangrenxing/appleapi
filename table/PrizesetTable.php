<?php
Globals::requireClass('Table');

class PrizesetTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_prizeset'
	);
}

Config::extend('PrizesetTable', 'Table');