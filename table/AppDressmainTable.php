<?php
Globals::requireClass('Table');

class AppDressmainTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'app_dress_main'
	);
	
	/**
	 * 随机查询获取搭配信息和单品信息
	 * gender		性别		默认女---2
	 * type			用于区分是刷新事件,还是随机查询其他的风格搭配[1---刷新,2---重新查询随机查询其他的风格]默认为随机查询所有的
	 * styles		风格ID拼接字符串
	 * recommlog	搭配ID拼接字符串
	 */
	public function randDressmain($gender,$type=null,$styles=null,$recommlog=null){
		if (!empty($gender) && trim($gender) != ""){
			$where[] = "gender=$gender";
		}else{
			$where[] = "gender=2";
		}
		if (!empty($type) && trim($type) != ""){
			if (!empty($styles) && trim($styles) != ""){
				if($type == 1){
					foreach(explode(",",$styles) as $keyST => $valueST){
						if($valueST != ""){
							$where[] = "style=$valueST";
						}
					}
				}else{
					foreach(explode(",",$styles) as $keySTY => $valueSTY){
						if($valueSTY != ""){
							$where[] = "style<>$valueSTY";
						}
					}
				}
			}
			if (!empty($recommlog) && trim($recommlog) != ""){
				foreach(explode(",",$recommlog) as $keyRE => $valueRE){
					if($valueRE != ""){
						$where[] = "id<>$valueRE";
					}
				}
			}
		}
		$data = $this->listRand($where,1);
		if(empty($data) && count($data) == 0){
			for ($i = 1; $i < count($where); $i++){
				unset($where[$i]);
			}
			$data = $this->listRand($where,1);
		}
		return $data[0];
	}
	
	
}

Config::extend('AppDressmainTable', 'Table');