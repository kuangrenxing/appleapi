<?php
Globals::requireClass('Table');

class MyitemTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_myitem'
	);
	
	public function getItemByIds($idArr)
	{
		$ids 	= '';
		$idArr 	= array_unique($idArr);
		$ids 	= implode(',' , $idArr);
		$ids   	= trim($ids , ',');
		
		$data   = array();
		if (count($idArr) && '' != $ids){
			$fieldsMyitem = "id,uid,title,price,img_url,ow,oh,cat_1,cat_2,cat_3";
        	$list  	= $this->listAllWithFields($fieldsMyitem, "id in (".$ids.") and del=0" , 'id desc');
        	if (is_array($list) && count($list)){
        		foreach ($list as $row){ $data[$row['id']] = $row;}
        	}
		}
		
		return $data;
	}
}

Config::extend('MyitemTable', 'Table');