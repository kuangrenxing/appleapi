<?php
Globals::requireClass('Table');

class PicEvaluateTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pic_evaluate'
	);
}

Config::extend('PicEvaluateTable', 'Table');