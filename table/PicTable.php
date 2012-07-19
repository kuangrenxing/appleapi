<?php
Globals::requireClass('Table');

class PicTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pic'
	);
	
	public function getPicByIds($idArr,$order = NULL)
	{
		$ids 	= '';
		$idArr 	= array_unique($idArr);
		$ids 	= implode(',' , array_filter($idArr));
		$ids   	= trim($ids , ',');
		
		$data   = array();
		if(!empty($order))
			$order_str = $order;
		else
			$order_str = 'id desc';

		if (count($idArr) && '' != $ids){
			$fieldsList = "id,uid,category,gender,title,img_url,ow,oh,likenum";
        	$list  	= $this->listAllWithFields($fieldsList,"id in (".$ids.")" , $order_str);        	
        	if (is_array($list) && count($list)){
        		foreach ($list as $row){ $data[$row['id']] = $row;}
        	}
		}
		
		return $data;
	}
	
	public function haveSalePic($findArray , $order = NULL, $pageSize = 0 , $pageId = 0)
	{
		if(isset($findArray['count']) && $findArray['count'])
			$selec = " count(*) as 'total' ";
		else
			$selec = " tp.* ";
		
		if(isset($findArray['count'])) unset($findArray['count']);
		
		
		$sql = "select ".$selec." 
				from tb_pic tp "
				. " JOIN tb_pic_relation tpr ON ( tpr.pid = tp.id )
				where tp.del = 0 and tp.flag = 1 ";
		
		$where = '';
		
		if(!empty($findArray))
		{
			foreach($findArray as $v)
			{
				$where .= ' AND '.$v;
			}
		}
		
		$sql .= $where;
		$sql .= ' GROUP BY tp.id ';
		
		if($order == NULL)
            $sql .= " order by tp.id desc ";
        else
            $sql .= " order by ".$order;

        if($pageSize > 0){
        	$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
        	$rowsInt   = $pageSize;
        	$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
        }
        //echo $sql;	
        $res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
}

Config::extend('PicTable', 'Table');