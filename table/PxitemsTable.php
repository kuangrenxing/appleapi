<?php
Globals::requireClass('Table');

class PxitemsTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pxitems'
	);
	
	
	
}

Config::extend('PxitemsTable', 'Table');