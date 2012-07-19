<?php
Globals::requireClass('Table');

class LikeTryoutTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_like_tryout'
	);
	
	/**
	 * 根据试用品ID查询试用品信息
	 */
	/*public function getRowTryouts($tryoutid){
		$strtryoutid = implode(",",$tryoutid);
		$sql = "select id as trid,tryout_name,original,trial_price from tb_tryout where id in ($strtryoutid);";
		$res  	= $this->database->query($sql);
		$data  	= $this->database->getList($res);
		return $data;
	}*/
	
}

Config::extend('LikeTryoutTable', 'Table');