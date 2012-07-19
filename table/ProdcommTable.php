<?php
Globals::requireClass('Table');

class ProdcommTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_prodcomm'
	);
}

Config::extend('ProdcommTable', 'Table');