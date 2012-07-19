<?php
Globals::requireClass('Table');

class PxtagTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pxtag'
	);
	
	public function getPxTags($pxid)
	{
		$sql = "select pt.*,t.* from tb_pxtag pt left join tb_tag t on pt.tag_id=t.id where px_id = $pxid";
		
		$res 	= $this->database->query($sql);
		$data	= $this->database->getList($res);
		
		return $data;
	}
}

Config::extend('PxtagTable', 'Table');
