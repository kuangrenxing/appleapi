<?php
Globals::requireClass('Table');

class PxstatTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pxstat_all'
	);
}

Config::extend('PxstatTable', 'Table');
