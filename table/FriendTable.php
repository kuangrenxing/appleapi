<?php
Globals::requireClass('Table');

class FriendTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_friend'
	);
	
	public function getFollow($uid , $limit = 9)
	{
		$sql = "select * from tb_friend where (uid = $uid and u_stat = ".FRIEND_STATUS_YES.") or "
				. " (friend_uid = $uid and f_stat = ".FRIEND_STATUS_YES.")"
					. " order by id desc limit 0 , $limit";
			
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getFans($uid , $limit = 9)
	{
		$sql = "select * from tb_friend where (friend_uid = $uid and u_stat = ".FRIEND_STATUS_YES.") or "
				. " (uid = $uid and f_stat = ".FRIEND_STATUS_YES.") "
					. " order by id desc limit 0 , $limit";
			
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function findFollows($findArray , $pageSize = 0 , $pageId = 0)
	{
		$uid = 0;
		if (isset($findArray['uid']) && $findArray['uid'])
			$uid = $findArray['uid'];
			
		$sql = "select * from tb_friend where (uid = $uid and u_stat = ".FRIEND_STATUS_YES.") or "
				. " (friend_uid = $uid and f_stat = ".FRIEND_STATUS_YES.")";
				
		if(!isset($findArray['orderBy']) || "" == $findArray['orderBy'])
            $sql .= " order by id desc ";
        else
            $sql .= " order by ".$findArray['orderBy'];
            
        if($pageSize > 0){
        	$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
        	$rowsInt   = $pageSize;
        	$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
        }
        
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function findFans($findArray , $pageSize = 0 , $pageId = 0)
	{
		$uid = 0;
		if (isset($findArray['uid']) && $findArray['uid'])
			$uid = $findArray['uid'];
			
		$sql = "select * from tb_friend where (friend_uid = $uid and u_stat = ".FRIEND_STATUS_YES.") or "
				. " (uid = $uid and f_stat = ".FRIEND_STATUS_YES.") ";
			
		if(!isset($findArray['orderBy']) || "" == $findArray['orderBy'])
            $sql .= " order by id desc ";
        else
            $sql .= " order by ".$findArray['orderBy'];
            
        if($pageSize > 0){
        	$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
        	$rowsInt   = $pageSize;
        	$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
        }
       
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getFollowNum($uid)
	{
		$sql = "select count(*) as cnt from tb_friend where (uid = $uid and u_stat = ".FRIEND_STATUS_YES.") or "
				. " (friend_uid = $uid and f_stat = ".FRIEND_STATUS_YES.")";
			
		$res = $this->database->query($sql);
        $row = $this->database->fetch($res);
        
        return intval($row['cnt']);
	}
	
	public function getFansNum($uid)
	{
		$sql = "select count(*) as cnt from tb_friend where (uid = $uid and u_stat = ".FRIEND_STATUS_YES.") or "
				. " (friend_uid = $uid and f_stat = ".FRIEND_STATUS_YES.")";
			
		$res = $this->database->query($sql);
        $row = $this->database->fetch($res);
        
        return intval($row['cnt']);
	}
	
//	public function checkFriend($uid , $friendUid)
//	{
//		$sql = "select count(*) as cnt from tb_friend where (uid = $uid and friend_uid = $friendUid and u_stat = ".FRIEND_STATUS_YES.") or "
//				. " (friend_uid = $uid and uid = $friendUid and f_stat = ".FRIEND_STATUS_YES.")";
//				
//		$res = $this->database->query($sql);
//        $row = $this->database->fetch($res);
//        
//        return intval($row['cnt']);
//	}
	
	public function checkFriend($uid , $friendUid)
	{
		$res = $this->getRow(array('uid' => $uid , 'friend_uid' => $friendUid));
        
		if (!$res)
			return 0;
		else 
			return 1;
	}
}

Config::extend('FriendTable', 'Table');
