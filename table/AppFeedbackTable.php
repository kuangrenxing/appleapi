<?php
Globals::requireClass('Table');

class AppFeedbackTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_app_feedback'
	);
}

Config::extend('AppFeedbackTable', 'Table');