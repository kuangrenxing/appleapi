<?php
require_once('./class/Config.php');
require_once('./class/Globals.php');

Globals::$self = new Globals(array('debug' => true));

$config = array(
	// db
	'server'		=> 'localhost',
	'username'		=> 'pinxiu',
	'password'		=> 'p#in2!xi-n',
	'database'		=> 'pinxiu',
	'charset'		=> 'utf8',
	'tablePrefix'	=> '',
	
	'viewEnabled'	=> true,
	'layoutEnabled'	=> true,
);