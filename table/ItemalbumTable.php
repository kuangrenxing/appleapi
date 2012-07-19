<?php
Globals::requireClass('Table');

class ItemalbumTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_item_album'
	);
}

Config::extend('ItemalbumTable', 'Table');