<?php
Globals::requireClass('Table');

class ProductTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_product'
	);
	
	public function getHotBrand($limit = 30 , $offset = 0)
	{
		$limit 	= intval($limit);
        $offset = intval($offset);
        $sql  = "select count(*) as cnt,b.*,p.bid  from tb_product p left join tb_brand b  on p.bid=b.id "
				. " where p.bid>0 and p.del = 0 group by p.bid order by cnt desc  ";
        if($limit >0)
            $sql .=' limit '.$offset.','.$limit;
        
        $res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getCateGroup($limit)
	{
		$limit 	= intval($limit);
        $sql  	= "select count(*) as cnt, cat_1  from tb_product "
					. "  group by cat_1 order by cnt desc  "
						. ' limit '.$limit;
        $res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function findProd($findArray , $pageSize = 0 , $pageId = 0)
	{
		$sql = "select * from tb_product ";
		$wh  = ' del =0 ';
        if(isset($findArray['del']))
            $wh = '1';
		if(isset($findArray['tb_product_color']) && "" != $findArray['tb_product_color'])
        	$sql .= " left join tb_product_color on tb_product.id = tb_product_color.pid ";
        $sql .= " where $wh ";
        if(isset($findArray['tb_product_color']) && "" != $findArray['tb_product_color'] ){
        	$color = intval($findArray['tb_product_color']);
        	if($color > 0 && $color < 30)
        		$sql .= "and tb_product_color.color_".$color."=1 ";
        }
  
        if(isset($findArray['like_title']) && "" != $findArray['like_title'])
            $sql .= " and title like '%".$findArray['like_title']."%'";
        if(isset($findArray['in_bid']) && "" != $findArray['in_bid'])
            $sql .= " and bid in ".$findArray['in_bid'];
		if(isset($findArray['bid']) && "" != $findArray['bid'])
            $sql .= " and bid = ".$findArray['bid'];
        if(isset($findArray['l_price']) && "" != $findArray['l_price'])
            $sql .= " and price >= ".$findArray['l_price'];
        if(isset($findArray['s_price']) && "" != $findArray['s_price'])
            $sql .= " and price <= ".$findArray['s_price'];
        
        if(isset($findArray['cat'])){
            $sql .= " and (cat_1='".$findArray['cat']."' ";
            $sql .= "  or cat_2='".$findArray['cat']."' ";
            $sql .= "  or cat_3='".$findArray['cat']."') ";
        }
       if(isset($findArray['l_cat']) && isset($findArray['s_cat'])){
            $sql .= " and ((cat_1>='".$findArray['l_cat']."' and cat_1<='".$findArray['s_cat']."') ";
            $sql .= " or (cat_2>='".$findArray['l_cat']."' and cat_2<='".$findArray['s_cat']."') ";
            $sql .= " or (cat_3>='".$findArray['l_cat']."' and cat_3<='".$findArray['s_cat']."')) ";
       }
       if (isset($findArray['no_cat_min']) && isset($findArray['no_cat_max'])){
       		$sql .= " and ((cat_1<'".$findArray['no_cat_min']."' or cat_1>='".$findArray['no_cat_max']."') ";
            $sql .= " and (cat_2<'".$findArray['no_cat_min']."' or cat_2>='".$findArray['no_cat_max']."') ";
            $sql .= " and (cat_3<'".$findArray['no_cat_min']."' or cat_3>='".$findArray['no_cat_max']."')) ";
       }	

        if(!isset($findArray['orderBy']) || "" == $findArray['orderBy'])
            $sql .= " order by tb_product.id desc ";
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
	
	public function getProdCount($findArray)
	{
		$sql = "select count(*) as cnt from tb_product ";
		$wh  = ' del =0 ';
        if(isset($findArray['del']))
            $wh = '1';
		if(isset($findArray['tb_product_color']) && "" != $findArray['tb_product_color'])
        	$sql .= " left join tb_product_color on tb_product.id = tb_product_color.pid ";
        $sql .= " where $wh ";
        if(isset($findArray['tb_product_color']) && "" != $findArray['tb_product_color'] ){
        	$color = intval($findArray['tb_product_color']);
        	if($color > 0 && $color < 30)
        		$sql .= "and tb_product_color.color_".$color."=1 ";
        }
  
        if(isset($findArray['like_title']) && "" != $findArray['like_title'])
            $sql .= " and title like '%".$findArray['like_title']."%'";
        if(isset($findArray['in_bid']) && "" != $findArray['in_bid'])
            $sql .= " and bid in ".$findArray['in_bid'];
        if(isset($findArray['bid']) && "" != $findArray['bid'])
            $sql .= " and bid = ".$findArray['bid'];
        if(isset($findArray['l_price']) && "" != $findArray['l_price'])
            $sql .= " and price >= ".$findArray['l_price'];
        if(isset($findArray['s_price']) && "" != $findArray['s_price'])
            $sql .= " and price <= ".$findArray['s_price'];
        
        if(isset($findArray['cat'])){
            $sql .= " and (cat_1='".$findArray['cat']."' ";
            $sql .= "  or cat_2='".$findArray['cat']."' ";
            $sql .= "  or cat_3='".$findArray['cat']."') ";
        }
       if(isset($findArray['l_cat']) && isset($findArray['s_cat'])){
            $sql .= " and ((cat_1>='".$findArray['l_cat']."' and cat_1<='".$findArray['s_cat']."') ";
            $sql .= " or (cat_2>='".$findArray['l_cat']."' and cat_2<='".$findArray['s_cat']."') ";
            $sql .= " or (cat_3>='".$findArray['l_cat']."' and cat_3<='".$findArray['s_cat']."')) ";
       }
       if (isset($findArray['no_cat_min']) && isset($findArray['no_cat_max'])){
       		$sql .= " and ((cat_1<'".$findArray['no_cat_min']."' or cat_1>='".$findArray['no_cat_max']."') ";
            $sql .= " and (cat_2<'".$findArray['no_cat_min']."' or cat_2>='".$findArray['no_cat_max']."') ";
            $sql .= " and (cat_3<'".$findArray['no_cat_min']."' or cat_3>='".$findArray['no_cat_max']."')) ";
       }
        
        $res = $this->database->query($sql);
        $row = $this->database->fetch($res);
        
        return intval($row['cnt']);
	}
	
	public function getProdLine($bid)
	{
		$bid = intval($bid);
    	if($bid <= 0) return false;
    	$prod_line_cat = $this->listAllWithFields('cat_1,cat_2,cat_3' , array('bid' => $bid) , 'id desc');
    
    	$prod_line	=array();
    	for($i = 0 ; $i < count($prod_line_cat) ; $i++){
        	$val = $prod_line_cat[$i];
        	for($j = 1 ; $j < 4 ; $j++){
            	$v = $val['cat_'.$j];
            	if($v>0 ){
                	$v = intval(substr(addslashes($v) , 0 , 1));
                	if(!in_array($v,$prod_line))
                    	$prod_line[] = $v;
            	}
        	}
    	}
    	sort($prod_line);
    	return $prod_line;
	}
	
	public function getProdByIds($idArr)
	{
		$ids 	= '';
		$idArr 	= array_unique($idArr);
		$ids 	= implode(',' , $ids);
		
		$data   = array();
		if (count($idArr) && '' != $ids){
        	$list  	= $this->listAll("id in (".$ids.")" , 'id desc');
        	if (is_array($list) && count($list)){
        		foreach ($list as $row){ $data[$row['id']] = $row;}
        	}
		}
		
		return $data;
	}
}

Config::extend('ProductTable', 'Table');