<?php
Globals::requireClass('Table');

class PicStarEvaluateTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pic_star_evaluate'
	);
	
	public function getStarPic($findArray , $order = NULL, $pageSize = 0 , $pageId = 0)
	{
		if(isset($findArray['count']) && $findArray['count'])
			$selec = " count(*) as 'total' ";
		else
			$selec = "t.* , i.id as 'i_id',i.sid,i.type ";
		
		if(isset($findArray['count'])) unset($findArray['count']);
		
		
		$sql = "select ".$selec." 
				from tb_pic_star_evaluate i "
				. " join tb_pic t on i.pid = t.id 
				where t.del = 0 and t.flag = 1 ";
		
		$where = '';
		
		if(!empty($findArray))
		{
			foreach($findArray as $v)
			{
				$where .= ' AND '.$v;
			}
		}
		
		$sql .= $where;
		
		if($order == NULL)
            $sql .= " group by t.id order by t.id desc ";
        else
            $sql .= " group by t.id order by ".$order;

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

Config::extend('PicStarEvaluateTable', 'Table');