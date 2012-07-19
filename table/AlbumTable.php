<?php
Globals::requireClass('Table');

class AlbumTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_album'
	);
}

Config::extend('AlbumTable', 'Table');