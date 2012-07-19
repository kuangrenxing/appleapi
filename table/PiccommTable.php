<?php
Globals::requireClass('Table');

class PiccommTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_piccomm'
	);
}

Config::extend('PiccommTable', 'Table');