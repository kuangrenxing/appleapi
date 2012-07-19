<?php
Globals::requireClass('Table');

class StarTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_star'
	);
	
	public function starCount($where='',$little_model = false)
	{
		if($little_model)
			$w = ' WHERE ts.type = 4 AND ts.is_show = 1 ';
		else
			$w = ' WHERE ts.type <> 4  AND ts.is_show = 1 ';
			
		if(!empty($where))
		{	
			foreach($where as $key => $value)
			{
				$w .= ' AND '.$key.' = '.$value;
			}
		}
		
		$sql = "SELECT ts.id,ts.name,ts.type,count(p.id) as 'num'
				FROM tb_star ts
				LEFT OUTER JOIN tb_pic_star_evaluate tpse ON (ts.id = tpse.sid)
				LEFT OUTER JOIN tb_pic p ON (tpse.pid = p.id AND p.del=0 AND flag=1)
				".$w."
				GROUP BY ts.id
				ORDER BY num desc";
		//echo $sql;
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
}

Config::extend('StarTable', 'Table');