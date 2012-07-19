<?php
Globals::requireClass('Table');

class AppDressprodTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'app_dress_prod'
	);
	
	/**
	 * 根据搭配ID查询搭配里的单品列表
	 * dress_id			搭配ID
	 */
	public function getDressProdList($dress_id){
		$sql = "select * from app_dress_prod where dress_id=$dress_id order by rank desc;";
		$res  	= $this->database->query($sql);
		$data = $this->database->getList($res);
		return $data;
	}
}

Config::extend('AppDressprodTable', 'Table');