<?php
Globals::requireClass('Table');

class PxbrandTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pxbrand'
	);
	
	public function getPxBrand($pxid)
	{
		$sql = "select pb.*,b.* from tb_pxbrand pb left join tb_brand b on pb.bid=b.id where px_id = $pxid";
		
		$res 	= $this->database->query($sql);
		$data	= $this->database->getList($res);
		
		return $data;
	}
	
	public function findHotBrand($limit = 30 , $offset = 0)
    {
        $limit 	= intval($limit);
        $offset = intval($offset);
        $sql  = "select count(*) as cnt,b.*,p.id as pxid from tb_pxbrand pb left join tb_brand b on pb.bid=b.id ";
        $sql .= " left join tb_pinxiu p on pb.px_id=p.id ";
        $sql .= " where p.status = 1 and name is not NULL group by pb.bid order by cnt desc  ";
        if($limit >0){
            $sql .=' limit '.$offset.','.$limit; 
        }
        $res 	= $this->database->query($sql);
		$data	= $this->database->getList($res);
		return $data;
    }
    
    public function findHotPxByBrand($brand , $num = 0)
    {
    	$num = intval($num);
    	
    	$sql = "select pb.*,p.*,pa.* from tb_pxbrand pb "
    			. "left join tb_pinxiu p on pb.px_id=p.id "
    				. "left join tb_pxstat_all pa on p.id=pa.px_id "
    					. "where p.status = 1 and pb.bid = $brand order by pa.view desc";
    		
    	if($num >0)
            $sql .=' limit 0,'.$num;
        
    	$res 	= $this->database->query($sql);
		$data	= $this->database->getList($res);
		return $data;
    }
    
    public function findRelatedBrand($id , $limit = 30)
    {
    	$id		= intval($id);
        $limit 	= intval($limit);
        if($limit <=0 || $limit >= 100)
            $limit = 30;
        $ret	= array();
        $sql	= "select px_id  from tb_pxbrand where bid = $id limit 200";
        
        $res 	= $this->database->query($sql);
		$list	= $this->database->getList($res);
		
        $px_ids = array();
        for($i = 0 ; $i < count($list) ; $i++)
        {
            if(!in_array($list[$i]['px_id'] , $px_ids))
                $px_ids[] = $list[$i]['px_id'];
        }
        if(count($px_ids) <= 0) return $ret;
        
        $in_px_ids = "(".implode("," , $px_ids).")";
        $sql_2	= "select distinct(bid)  from tb_pxbrand where px_id in $in_px_ids  and bid <> $id limit $limit";
        $res_2 	= $this->database->query($sql_2);
		$blist	= $this->database->getList($res_2);
		
        for($i = 0 ; $i < count($blist) ; $i++)
            $ret[]  = $blist[$i]['bid'];
        return $ret;
    }
}

Config::extend('PxbrandTable', 'Table');