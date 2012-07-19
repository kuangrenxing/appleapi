<?php
Globals::requireClass('Table');

class FavoritesTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_my_favorites'
	);
}

Config::extend('FavoritesTable', 'Table');