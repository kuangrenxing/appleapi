<?php
Globals::requireClass('Table');

class MaterialTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_material'
	);
}

Config::extend('MaterialTable', 'Table');