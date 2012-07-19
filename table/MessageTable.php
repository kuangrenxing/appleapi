<?php
Globals::requireClass('Table');

class MessageTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_message'
	);
}

Config::extend('MessageTable', 'Table');