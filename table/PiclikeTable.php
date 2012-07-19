<?php
Globals::requireClass('Table');

class PiclikeTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_like_pic'
	);
}

Config::extend('PiclikeTable', 'Table');