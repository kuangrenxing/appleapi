<?php
Globals::requireClass('Table');

class TimeaxisTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_timeaxis'
	);
	
	/**
	 * 根据用户组查询用户的物品列表数量
	 */
	public function articlecount($where = null){
		$sql = "select count(*) count from tb_timeaxis left join tb_user on tb_user.id=tb_timeaxis.uid";
		if(isset($where['agroup']) && "" != $where['agroup'])
            $sql .= " where tb_user.agroup=".$where['agroup'];

		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data[0]["count"];
		
		/* $timeaxisArr = $this->database->listAllWithFields("tb_timeaxis","uid");
		foreach($timeaxisArr as $i=>$timeaxis)
		{
			$uidArr[] = $timeaxis['uid'];
		}
		$uidStr = implode(",", $uidArr);
		$whereUser = "id in ($uidStr)";
		
		if(isset($where['agroup']) && "" != $where['agroup'])
			$whereUser.=" and agroup=".$where['agroup'];		

		$count  = $this->database->listCount("tb_user",$whereUser);
		return $count; */
	}
	
	/**
	 * 根据用户组查询用户的物品列表
	 */
	public function articlelist($where = null , $order = null , $pageSize = 0 , $pageId = 0){

		/* if(isset($order) && $order != ""){			
			$timeaxisArr = $this->database->listAllWithFields("tb_timeaxis","id,uid,postid,type","",$order);
		}else{
			$timeaxisArr = $this->database->listAllWithFields("tb_timeaxis","id,uid,postid,type","","ctime desc");			
		}
		
		foreach($timeaxisArr as $i=>$timeaxis)
		{
			$uidArr[] = $timeaxis['uid'];
		}
		$uidStr = implode(",", $uidArr);
		
		
		if($pageSize > 0){
			$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
			$rowsInt   = $pageSize;
		}
		
		$userArr  = $this->database->listAllWithFields("tb_user","id,head_pic,email,sex,city",null,null,$rowsInt,$offsetInt);
		
		foreach($userArr as $iu=>$user)
		{
			foreach($timeaxisArr as $it=>$timeaxis)
			{
				if($userArr[$iu]['id'] == $timeaxisArr[$it]['uid'])
				{
					$data[] = array_merge($userArr[$iu],$timeaxisArr[$it]);
				}
			}
		} 
		
		
		return $data; */
		$sql = "select * from tb_timeaxis left join tb_user on tb_user.id=tb_timeaxis.uid ";
		if(isset($where['agroup']) && "" != $where['agroup'])
			$sql .= " where tb_user.agroup=".$where['agroup'];
		
		if(isset($order) && $order != ""){
			$sql = $sql . " order by tb_timeaxis.".$order;
		}else{
			$sql = $sql . " order by tb_timeaxis.ctime desc";
		}
		
		if($pageSize > 0){
			$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
			$rowsInt   = $pageSize;
			$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
		}
		
		$res  	= $this->database->query($sql);
		$data  	= $this->database->getList($res);
		
		return $data;
		
	}
}

Config::extend('TimeaxisTable', 'Table');