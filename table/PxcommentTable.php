<?php
Globals::requireClass('Table');

class PxcommentTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pxcomment'
	);
}

Config::extend('PxcommentTable', 'Table');