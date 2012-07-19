<?php
Globals::requireClass('Table');

class PicRelationTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_pic_relation'
	);
	
	public function getRelationByPid($pid_arr,$ord = '')
	{
		if(!is_null($pid_arr))
			$sub_str = implode(',',$pid_arr);
		else
			return false;
		
		$order = empty($ord)?'':' ORDER BY '.$ord;	
		
		$sql = "SELECT tm.*,tpr.pid as 'tpid',tpr.glike,tpr.mid
				FROM tb_pic_relation tpr
				JOIN tb_myitem tm ON (tpr.mid = tm.id AND tm.del = 0 AND tm.flag = 1)
				WHERE tpr.pid in (".$sub_str.')'.$order;		
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
		
		$ret_data = array();
		$uid_arr = array();
		if(!is_null($data))
		{
			foreach($data as $v)
			{
				$id = $v['tpid'];
				/*$uid = $v['uid'];
				$uid_arr[$uid] = $uid;*/
				$ret_data[$id][] = $v;
			}
		}
		
		
		/*if(!empty($uid_arr))
		{
			$sql = "SELECT id,username,head_pic 
					FROM tb_user 
					WHERE id in (".implode($uid_arr).")";
			echo $sql;
			$res  	= $this->database->query($sql);
			$data  	= $this->database->getList($res);
		}
		print_r($ret_data);
		foreach($ret_data as $k=>$v)
		{
			foreach($v as $ke=>$val)
			{
				foreach($data as $key=>$value)
				{
					if($ret_data[$k]['uid'] == $value['id'])
					{
						$ret_data[$k]['username'] = $value['username'];
						$ret_data[$k]['head_pic'] = $value['head_pic'];
					}
				}
			}
			
		}*/
       	
        return $ret_data;
	}
	
}

Config::extend('PicRelationTable', 'Table');