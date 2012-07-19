<?php
Globals::requireClass('Table');

class RecommendTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_myitem_recommend'
	);
	
	public function getMyItem($id){
		$sql = "select tb_myitem_recommend.* from tb_myitem_recommend left join tb_myitem on tb_myitem_recommend.itemid=tb_myitem.id where tb_myitem_recommend.id=$id;";
		$res  	= $this->database->query($sql);
		$data  	= $this->database->getList($res);
		$resdata["id"] = $data[0]["rid"];
		$resdata["itemid"] = $data[0]["itemid"];
		$resdata["title"] = $data[0]["title"];
		$resdata["recommendorder"] = $data[0]["recommendorder"];
		$resdata["recommenddate"] = date('Y-m-d',$data[0]["recommenddate"]);
		$resdata["recommendimgurl"] = $data[0]["recommendimgurl"];
		$resdata["recommendimgurlfile"] = $data[0]["recommendimgurlfile"];
		$resdata["delstatus"] = $data[0]["delstatus"];
		return $resdata;
	}
	
	/**
	 * 根据tags查询随机单品搭配模板
	 */
	public function getRowRecommend($itemid){
		//$sql = "SELECT * FROM tb_myitem_recommend where delstatus=1 and itemid=".$itemid;
		$sql = "select tb_myitem.id as itemid,tb_myitem.title,tb_myitem_recommend.id as rid,tb_myitem_recommend.recommendorder,tb_myitem_recommend.recommenddate,tb_myitem_recommend.recommendimgurl,tb_myitem_recommend.recommendimgurlfile,tb_myitem_recommend.delstatus from tb_myitem left join tb_myitem_recommend on tb_myitem.id=tb_myitem_recommend.itemid where tb_myitem.id=$itemid;";
		$res  	= $this->database->query($sql);
		$data  	= $this->database->getList($res);
		$resdata["id"] = $data[0]["rid"];
		$resdata["itemid"] = $data[0]["itemid"];
		$resdata["title"] = $data[0]["title"];
		$resdata["recommendorder"] = $data[0]["recommendorder"];
		$resdata["recommenddate"] = date('Y-m-d',$data[0]["recommenddate"]);
		$resdata["recommendimgurl"] = $data[0]["recommendimgurl"];
		$resdata["recommendimgurlfile"] = $data[0]["recommendimgurlfile"];
		$resdata["delstatus"] = $data[0]["delstatus"];
		return $resdata;
	}
	
	/**
	 * 随机查询风格里的适合单品
	 * gender			用户性别
	 * type				用于区分是刷新事件,还是随机查询其他的风格搭配[1---刷新,2---重新查询随机查询其他的风格]默认为随机查询所有的
	 * styles			风格ID拼接字符串
	 * recommlog			单品ID拼接字符串
	 */
	public function randStyleMyitem($gender,$type=null,$styles=null,$recommlog=null){
		if (!empty($gender) && trim($gender) != ""){
			$where[] = "gender=$gender";
		}else{
			$where[] = "gender=2";
		}
		if (!empty($type) && trim($type) != ""){
			if (!empty($styles) && trim($styles) != ""){
				if($type == 1){
					foreach(explode(",",$styles) as $keyST => $valueST){
						$where[] = "(LOCATE(',$valueST,',tag_cat_id) > 0 or tag_cat_id=$valueST)";
					}
				}else{
					foreach(explode(",",$styles) as $keySTY => $valueSTY){
						$where[] = "(LOCATE(',$valueSTY,',tag_cat_id) = 0 and tag_cat_id<>$valueSTY)";
					}
				}
			}
			if (!empty($recommlog) && trim($recommlog) != ""){
				foreach(explode(",",$recommlog) as $keyRE => $valueRE){
					$where[] = "id<>$valueRE";
				}
			}
		}
		$data = $this->listRand($where,3);
		if(empty($data) && count($data) == 0){
			for ($i = 1; $i < count($where); $i++){
				unset($where[$i]);
			}
			$data = $this->listRand($where,3);
		}
		return $data;
	}
}

Config::extend('RecommendTable', 'Table');