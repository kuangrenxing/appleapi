<?php
Globals::requireClass('Table');

class PinxiuTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pinxiu'
	);
	
	public function findPX($findArray , $pageSize = 0 , $pageId = 0)
	{
		$sql ="select p.*,pall.* from tb_pxstat_all pall ";
        $sql.=" left join tb_pinxiu p on pall.px_id = p.id  where 1 ";
        if(isset($findArray['like_title']) && "" != $findArray['like_title'])
            $sql .= " and p.title like '%".$findArray['like_title']."%'";
        if(isset($findArray['maincat_id']) && "" != $findArray['maincat_id'])
            $sql .= " and p.maincat_id='".$findArray['maincat_id']."'";
        if(isset($findArray['subcat_id']) && "" != $findArray['subcat_id'])
            $sql .= " and p.subcat_id='".$findArray['subcat_id']."'";
        if(isset($findArray['in_subcat_id']) && "" != $findArray['in_subcat_id'])
            $sql .= " and p.subcat_id in ".$findArray['in_subcat_id'];
        if(isset($findArray['status']) && "" != $findArray['status'])
            $sql .= " and p.status= ".$findArray['status'];
        if(isset($findArray['uid']) && "" != $findArray['uid'])
            $sql .= " and p.uid='".$findArray['uid']."'";
        
        if(isset($findArray['l_sum_price']) && "" != $findArray['l_sum_price'])
            $sql .= " and p.sum_price>='".$findArray['l_sum_price']."'";
        if(isset($findArray['s_sum_price']) && "" != $findArray['s_sum_price'])
            $sql .= " and p.sum_price <='".$findArray['s_sum_price']."'";
        if(isset($findArray['sum_price']) && "" != $findArray['sum_price'])
            $sql .= " and p.sum_price='".$findArray['sum_price']."'";
        
        if(isset($findArray['l_time_created']) && "" != $findArray['l_time_created'])
            $sql .= " and p.time_created>='".$findArray['l_time_created']."'";
        if(isset($findArray['s_time_created']) && "" != $findArray['s_time_created'])
            $sql .= " and p.time_created <='".$findArray['s_time_created']."'";
 

        if(!isset($findArray['orderBy']) || "" == $findArray['orderBy'])
            $sql .= " order by pall.id desc ";
        else
            $sql .= " order by ".$findArray['orderBy']." ".$findArray['orderFlag'];

        if($pageSize > 0){
        	$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
        	$rowsInt   = $pageSize;
        	$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
        }
        	
        $res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getPxCount($findArray)
	{
		$sql = " select count(*) as cnt from tb_pxstat_all pall "
				. " left join tb_pinxiu p on pall.px_id = p.id where 1 ";
		if(isset($findArray['status']) && "" != $findArray['status'])
            $sql .= " and p.status='".$findArray['status']."'";
        if(isset($findArray['maincat_id']) && "" != $findArray['maincat_id'])
            $sql .= " and p.maincat_id='".$findArray['maincat_id']."'";
        if(isset($findArray['subcat_id']) && "" != $findArray['subcat_id'])
            $sql .= " and p.subcat_id='".$findArray['subcat_id']."'";
        if(isset($findArray['in_subcat_id']) && "" != $findArray['in_subcat_id'])
            $sql .= " and p.subcat_id in ".$findArray['in_subcat_id'];
        
        $res = $this->database->query($sql);
        $row = $this->database->fetch($res);
        
        return intval($row['cnt']);
	}
	
	public function findMyPx($findArray , $pageSize = 0 , $pageId = 0)
	{
		$sql ="select $col from tb_pxstat_all pall "
				. " left join tb_pinxiu p on pall.px_id = p.id  where 1 ";
        if(isset($findArray['like_title']) && "" != $findArray['like_title'])
            $sql .= " and p.title like '%".$findArray['like_title']."%'";
        if(isset($findArray['maincat_id']) && "" != $findArray['maincat_id'])
            $sql .= " and p.maincat_id='".$findArray['maincat_id']."'";
        if(isset($findArray['subcat_id']) && "" != $findArray['subcat_id'])
            $sql .= " and p.subcat_id='".$findArray['subcat_id']."'";
        if(isset($findArray['in_subcat_id']) && "" != $findArray['in_subcat_id'])
            $sql .= " and p.subcat_id in ".$findArray['in_subcat_id'];
        if(isset($findArray['status']) && "" != $findArray['status'])
            $sql .= " and p.status= ".$findArray['status'];
        if(isset($findArray['uid']) && "" != $findArray['uid'])
            $sql .= " and p.uid='".$findArray['uid']."'";
        
        if(isset($findArray['l_sum_price']) && "" != $findArray['l_sum_price'])
            $sql .= " and p.sum_price>='".$findArray['l_sum_price']."'";
        if(isset($findArray['s_sum_price']) && "" != $findArray['s_sum_price'])
            $sql .= " and p.sum_price <='".$findArray['s_sum_price']."'";
        if(isset($findArray['sum_price']) && "" != $findArray['sum_price'])
            $sql .= " and p.sum_price='".$findArray['sum_price']."'";
        
        if(isset($findArray['l_time_created']) && "" != $findArray['l_time_created'])
            $sql .= " and p.time_created>='".$findArray['l_time_created']."'";
        if(isset($findArray['s_time_created']) && "" != $findArray['s_time_created'])
            $sql .= " and p.time_created <='".$findArray['s_time_created']."'";
 

        if(!isset($findArray['orderBy']) || "" == $findArray['orderBy'])
            $sql .= " order by pall.id desc ";
        else
            $sql .= " order by ".$findArray['orderBy']." ".$findArray['orderFlag'];

        if($pageSize > 0){
        	$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
        	$rowsInt   = $pageSize;
        	$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
        }
        
        $res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getPxByIds($idArr)
	{
		$ids 	= '';
		$idArr 	= array_unique($idArr);
		$ids 	= implode(',' , $idArr);
		$ids   	= trim($ids , ',');
		
		$data   = array();
		if (count($idArr) && '' != $ids){
			$fieldsList = "id,uid,username,head_pic,px_pic,title,sex";
        	$list  	= $this->listAllWithFields($fieldsList,"id in (".$ids.")" , 'id desc');
        	if (is_array($list) && count($list)){
        		foreach ($list as $row){ $data[$row['id']] = $row;}
        	}
		}
		
		return $data;
	}
}

Config::extend('PinxiuTable', 'Table');
