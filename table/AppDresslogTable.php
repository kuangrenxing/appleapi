<?php
Globals::requireClass('Table');

class AppDresslogTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'app_dress_log'
	);
	
	/**
	 * 获取适合的风格ID
	 * userid		用户ID
	 * $keyID		取分数的第几个的风格ID[开始位置从:0]
	 */
	public function getDressStylesList($userid,$keyID=null){
		if (empty($keyID) && trim($keyID) == ""){
			$keyID = 0;
		}
		$sql = "select * from app_dress_log where uid=$userid and type=1 order by average desc limit 5;";
		$res  	= $this->database->query($sql);
		$data = $this->database->getList($res);
		return $data[$keyID]["style"];
	}
}

Config::extend('AppDresslogTable', 'Table');