<?php
Globals::requireClass('Table');

class TweetCommTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_tweetcomm'
	);
}

Config::extend('TweetCommTable', 'Table');