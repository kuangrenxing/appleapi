<?php
	Globals::requireClass('Table');
	class PicTagTable extends Table
	{
		public static $defaultConfig = array(
			'table' => 'tb_pictag'
		);
	}
	Config::extend('PicTagTable', 'Table');
?>