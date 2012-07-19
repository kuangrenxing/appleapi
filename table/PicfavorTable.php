<?php
Globals::requireClass('Table');

class PicfavorTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_favor_pic'
	);
}

Config::extend('PicfavorTable', 'Table');