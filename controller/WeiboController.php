<?php
Globals::requireClass('Controller');
Globals::requireTable('User');
Globals::requireTable('Userstat');
Globals::requireTable('Tweet');
Globals::requireTable('Usermsg');
Globals::requireTable('Message');
Globals::requireTable('Friend');
Globals::requireTable('Likeitem');
Globals::requireTable('Myitem');
Globals::requireTable('Prodcomm');

class WeiboController extends Controller
{
	protected $user;
	protected $userstat;
	protected $tweet;
	protected $usermsg;
	protected $message;
	protected $friend;
	protected $likeitem;
	protected $myitem;
	protected $prodcomm;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->user  		= new UserTable($config);
		$this->tweet		= new TweetTable($config);
		$this->usermsg		= new UsermsgTable($config);
		$this->userstat		= new UserstatTable($config);
		$this->message		= new MessageTable($config);
		$this->friend		= new FriendTable($config);
		$this->likeitem		= new LikeitemTable($config);
		$this->myitem		= new MyitemTable($config);
		$this->prodcomm		= new ProdcommTable($config);
	}
	
	//http://192.168.1.21/iphoneweibo/?m=weibo&a=friendlist&uid=159881
	/*
	 * 用户的关注用户信息列表
	 * uid		用户ID
	 * page		页码[默认:0或者1]
	 */
	public function friendlistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$params = $this->getParams('uid,page');
		if(empty($params['uid']) || trim($params['uid']) == ''){
			echo "";
			return;
		}
		$uid = $params['uid'];
		$pageId = $params['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		//用户关注信息数组
		$user_follow = array();
		$user_follow["uid"] = $uid;
		//用户关注人的总数量
		$followData = $this->userstat->getRow(array('uid' => $uid));
		$user_follow["follow"] = $followData["follow"];
		
		$where['uid'] = $uid;
		$count = $this->friend->listCount($where);
		$pageSize = 20;
		if($count > 0){
			if($pageId > ceil($count/$pageSize)){
				echo "";
				return;
			}
		}else{
			echo "";
			return;
		}
		
		$data = $this->friend->listPage($where , 'id desc' , $pageSize , $pageId);
		
		$friendArr = array();
		foreach ($data as $k=>$v){
			$friendArr[$k]["friend_uid"] = $v['friend_uid'];
			$friendArr[$k]["friend_info"] = $this->user->findUserStat(array('in_uid' => $v['friend_uid']));
			$friendArr[$k]["friend_info"] = $friendArr[$k]["friend_info"][0];
		}
		
		
		
		$user_follow["friend_list"] = $friendArr;
		
		echo $this->customJsonEncode($user_follow);
	}
	
	//http://192.168.1.21/iphoneweibo/?m=weibo&a=friend&uid=159881&friend_uid=3608&status=0
	/*
	 * 管理用户关注的用户信息
	 * uid			用户ID
	 * friend_uid	关注用户ID
	 * status		关注状态[1---添加关注,0---取消关注]参数可以为空,默认：添加关注
	 */
	public function friendAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$params = $this->getParams('uid,friend_uid,status');
		if(empty($params['uid']) || trim($params['uid']) == ''){
			echo "";
			return;
		}
		if(empty($params['friend_uid']) || trim($params['friend_uid']) == ''){
			echo "";
			return;
		}
		if(isset($params['status']) && trim($params['status']) == ''){
			$status = 1;
		}else{
			$status = $params['status'];
		}
		
		$uid = $params['uid'];
		$friend_uid = $params['friend_uid'];
		
		$followData = $this->userstat->getRow(array('uid' => $uid));

		if(!$followData){
			echo "";
			return;
		}
		if($status == 1){
			$addF['uid'] = $uid;
			$addF['friend_uid'] = $friend_uid;
			$addF['time_created'] = time();
		    $fID = $this->friend->add($addF , true);
			$arrFollow["follow"] = $followData["follow"]+1;
		}else{
			$where[] = "uid=$uid";
			$where[] = "friend_uid=$friend_uid";
		    $this->friend->delete($where);
			$arrFollow["follow"] = $followData["follow"]-1;
		}
		if(empty($followData["id"]) || trim($followData["id"]) == ''){
			echo "";
			return;
		}
		$result = $this->userstat->modify($arrFollow, $followData["id"]);
		if($result)
		{
			echo $this->customJsonEncode(array("true"));
		}
		else
		{
			echo $this->customJsonEncode(array("false"));
		}
	}
	
	//http://192.168.1.21/iphoneweibo/?m=weibo&a=fanslist&uid=155601
	/*
	 * 用户的粉丝用户信息列表
	 * uid		用户ID
	 * page		页码[默认:0或者1]
	 */
	public function fanslistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$params = $this->getParams('uid,page');
		if(empty($params['uid']) || trim($params['uid']) == ''){
			echo "";
			return;
		}
		$uid = $params['uid'];
		$pageId = $params['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		//用户粉丝信息数组
		$user_fans = array();
		$user_fans["uid"] = $uid;
		//用户粉丝的总数量
		$followData = $this->userstat->getRow(array('uid' => $uid));
		if(!$followData){
			exit;
		}
		$user_fans["fans"] = $followData["fans"];
		
		$where['friend_uid'] = $uid;
		$count = $this->friend->listCount($where);
		$pageSize = 20;
		if($count > 0){
			if($pageId > ceil($count/$pageSize)){
				echo "";
				return;
			}
		}else{
			echo "";
			return;
		}
		
		$data = $this->friend->listPage($where , 'id desc' , $pageSize , $pageId);
		
		$friendArr = array();
		foreach ($data as $k=>$v){
			$friendArr[$k]["fans_uid"] = $v['uid'];
			$friendArr[$k]["fans_info"] = $this->user->findUserStat(array('in_uid' => $v['uid']));
			$friendArr[$k]["fans_info"] = $friendArr[$k]["fans_info"][0];
		}
		$user_fans["fans_list"] = $friendArr;
		
		echo $this->customJsonEncode($user_fans);
	}
	
	//http://192.168.1.21/iphoneweibo/?m=weibo&a=prodlikelist&uid=159881
	/*
	 * 用户的喜欢单品信息列表
	 * uid		用户ID
	 * page		页码[默认:0或者1]
	 */
	public function prodlikelistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('uid,page');
		if(empty($params['uid']) || trim($params['uid']) == ''){
			echo "";
			return;
		}
		$uid = $params['uid'];
		$pageId = $params['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		//用户喜欢信息数组
		$user_fans = array();
		$user_fans["uid"] = $uid;
		//用户喜欢的总数量
		$followData = $this->userstat->getRow(array('uid' => $uid));
		$user_fans["likenum"] = $followData["likenum"];
		
		$where['uid'] = $uid;
		$count = $this->likeitem->listCount($where);
		$pageSize = 20;
		if($count > 0){
			if($pageId > ceil($count/$pageSize)){
				echo "";
				return;
			}
		}else{
			echo "";
			return;
		}
		
		$data = $this->likeitem->listPage($where , 'id desc' , $pageSize , $pageId);
	
		
		$friendArr = array();
		foreach ($data as $k=>$v){
			$friendArr[$k]["likenum_itemid"] = $v['itemid'];
			$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
				
			$friendArr[$k]["likenum_info"] = $this->myitem->getRowWithFields($fieldsMyitem,array('id' => $v['itemid']));
			//$friendArr[$k]["likenum_info"] = $friendArr[$k]["likenum_info"][0];
		}
		$user_fans["likenum_list"] = $friendArr;
		
		echo $this->customJsonEncode($user_fans);
	}
	
	//http://192.168.1.21/iphoneweibo/?m=weibo&a=wblist&uid=2234&type=f&page=2
	/**
	 * 用户关注和粉丝的微博列表
	 * uid			用户ID
	 * type			用户消息类型[n---一般消息,f---相互关注]默认:n
	 * page			页码[默认:0或者1]
	 */
	public function wblistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('uid,type,page');
		if(empty($params['uid']) || trim($params['uid']) == ''){
			echo "";
			return;
		}
		$uid = $params['uid'];
		$tp = $params['type'];
		$pageId = $params['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		
		if ('f' == $tp){
			$type = USER_MSG_TYPE_FOLLOWS;
		}else{ 
			$type = USER_MSG_TYPE_NORMAL;
		} 
		
		$where 		= array('receive_uid' => $uid , 'type' => $type);
		$order		= "id desc";
		$count 		= $this->usermsg->listCount($where);
		$pageSize 	= 20;
		if($count > 0){
			if($pageId > ceil($count/$pageSize)){
				echo "";
				return;
			}
		}else{
			echo "";
			exit;
		}
		
		$data 	= $this->usermsg->listPageWithFields("id,receive_uid,act_id",$where,$order,$pageSize,$pageId);
		
		foreach ($data as $key => $row){
			
			$tweetInfo = $this->tweet->getRowWithFields("id,conid,type,uid,message,img_url,head_uid",$row['act_id']);
			if (!$tweetInfo)
				continue;
			$data[$key]['tweetInfo'] = $tweetInfo;
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 根据单品ID获取单品信息，发布单品的用户信息，单品的评论信息
	 * myid			单品ID
	 * page			页码[默认:0或者1]
	 */
	public function myitemAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$myitem = array();
		
		$params = $this->getParams('myid,page');
		$myid = $params["myid"];
		if (!$myid){
			echo "";
			return;
		}
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
		
		$rowMyitem = $this->myitem->getRowWithFields($fieldsMyitem,$myid);
		if (!$rowMyitem){
			echo "";
			return;
		}
		//$myitem["myitem"] = array('id'=>$rowMyitem["id"],'uid'=>$rowMyitem["uid"],'title'=>$rowMyitem["title"],'price'=>$rowMyitem["price"],'img_url'=>IMAGE_DOMAIN.$rowMyitem["img_url"]);
		$rowMyitem["img_url"] = IMAGE_DOMAIN.$rowMyitem["img_url"];
		$rowMyitem["source_url"] = SOURCE_DOMAIN."?m=go&id=".$rowMyitem["id"];
		$myitem["myitem"] = $rowMyitem;
		$fieldsUser = "id,username,head_pic,email,sex,city";
		
		$userInfo = $this->user->getRowWithFields($fieldsUser,$rowMyitem['uid']);
		$userInfo['link'] = "./?m=user&uid=".$rowMyitem['uid'];
		$userInfo['head'] = IMAGE_DOMAIN.getUserPath($userInfo['head_pic'] , 36);
		$userInfo['head_pic'] = IMAGE_DOMAIN.$userInfo['head_pic'];
		
		$myitem["userinfo"] = $userInfo;
		
		//更新浏览数
	
		if(!empty($rowMyitem['id']) || trim($rowMyitem['id']) != '' || trim($rowMyitem['id']) != '0'){
			$this->myitem->update(array("view=view+".rand(10,30)) , $rowMyitem['id']);
		}
		
		$pageId = $params['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		$where 		= array('item_id' => $myid , 'zf_id' => 0 , 'pl_id' => 0);
		$count		= $this->prodcomm->listCount($where);
		$pageSize	= 10;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$fieldsProdcomm = "";
		$fieldsProdcomm = "id,prod_id,item_id,uid,username,head_pic,comment";
		$data	= $this->prodcomm->listPageWithFields($fieldsProdcomm,$where, 'id desc', $pageSize, $pageId);
		
		$uidArr = array();
		foreach ($data as $pcRow){
			$uidArr[] = $pcRow['uid'];
		}
		unset($pcRow);
		if (count($uidArr)){
			$userList = $this->user->getUserByIds($uidArr);
			foreach ($data as $keyd => $valued){
				$pusinfo = array();
				$pusinfo["id"] = $userList[$valued["uid"]]["id"];
				$pusinfo["username"] = $userList[$valued["uid"]]["username"];
				$pusinfo["head_pic"] = IMAGE_DOMAIN.$userList[$valued["uid"]]["head_pic"];
				
				$data[$keyd]["userinfo"] = $pusinfo;
				
				$data[$keyd]['head_pic'] = IMAGE_DOMAIN.$data[$keyd]['head_pic'];
				unset($pusinfo);
			}
			unset($userList);
		}
		
		$myitem["prodcomm"] = $data;
		
		echo $this->customJsonEncode($myitem);
	}
	
	/**
	 * 由于php的json扩展自带的函数json_encode会将汉字转换成unicode码
	 * 所以我们在这里用自定义的json_encode，这个函数不会将汉字转换为unicode码
	*/
	public function customJsonEncode($a = false) {
		if(is_null($a)) return 'null';
		if($a === false) return 'false';
		if($a === true) return 'true';
		if(is_scalar($a)){
			if(is_float($a)){
				//Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}
			if(is_string($a)){
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\', '/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}else{
				return $a;
			}
		}
		$isList = true;
		for($i = 0,reset($a);$i < count($a);$i++,next($a)){
			if(key($a) !== $i){
				$isList = false;
				break;
			}
		}
		$result = array();
		if($isList){
			foreach($a as $v) $result[] = $this->customJsonEncode($v);
			return '[' . join(',', $result) . ']';
		}else{
			foreach ($a as $k => $v) $result[] = $this->customJsonEncode($k).':'.$this->customJsonEncode($v);
			return '{' . join(',', $result) . '}';
		}
	}
	
	
}

Config::extend('WeiboController', 'Controller');
