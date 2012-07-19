<?php
Globals::requireClass('Controller');

Globals::requireTable('Pinxiu');
Globals::requireTable('Myitem');
Globals::requireTable('Pxtag');
Globals::requireTable('Pxitems');
Globals::requireTable('User');

class PxController extends Controller
{
	protected $pinxiu;
	protected $myitem;
	protected $pxtag;
	protected $pxitems;
	protected $user;

	public function __construct($config = null) 
	{
		parent::__construct($config);

		$this->pinxiu = new PinxiuTable($config);
		$this->myitem = new MyitemTable($config);
		$this->pxtag = new PxtagTable($config);
		$this->pxitems = new PxitemsTable($config);
		$this->user  = new UserTable($config);

	}
	
	/**
	 * 根据搭配分类查询搭配列表
	 * occasion		搭配分类{女[1--时尚摩登,2--热辣迷人,4--气场女王,5--优雅性感,7--清新森系,8--淑女知性,10--中性简约,11--甜美可爱],男[3--正统高贵,6--运动休闲,9--品位IT男,12--英伦风范]}
	 */
	public function indexAction()
	{			
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$occasion = $this->getIntParam('occasion');	
		if(!$occasion){
			echo "";
			exit;
		}else {
			$n_where['occasion'] = $occasion;
		}
		
		$n_where = array();
		$n_where['status']=1;			
		
		//pinxiu搜索字段
		$fields="id, uid, username, head_pic, px_pic, title, sex, likenum";
		$order = "id desc";
		$count = $this->pinxiu->listCount($n_where);
		if($count==0){
			exit;
		}
		$pageSize = 12;//12条一页		
		$paging = $this->getPaging($count, $pageSize, $pageId, 3);		
		$data = $this->pinxiu->listPageWithFields($fields,$n_where, $order, $pageSize, $pageId);
		if(!$data){
			echo "";
			exit;
		}
		
		$uidArr = array();
		foreach ($data as $k=>$row)	{
			$uidArr[] = $row['uid'];
			$data[$k]['px_pic'] = IMAGE_DOMAIN.$row['px_pic'];
			$data[$k]['head_pic'] = IMAGE_DOMAIN.$row['head_pic'];
		}
		
		unset($k , $row);
		//获取用户数据
		if (count($uidArr)){
			//得到多个用户信息
			$userList = $this->user->getUserByIds($uidArr);
			//只取指定字段赋值$newUserList
			foreach($userList as $i => $value_u){
				$newUserList[$i] = array(
						'id'=>$value_u['id'],
						'username'=>$value_u['username'],
						'head_pic' => IMAGE_DOMAIN.$value_u['head_pic'],
						'sex' => $value_u['sex']
						);
			}
			//用户信息加入$data
			foreach($data as $k => $row){
				if(isset($newUserList[$row['uid']])){
					$data[$k]['userinfo'] = $newUserList[$row['uid']];
				}
			}
			unset($userList ,$newUserList);
		}
		

		$pxIds = array();
		$prodIds = array();
		if ($data) {
			foreach ($data as $k => $v) {
				$pxIds[] = $v['id'];
			}
		}
		unset($k, $v);
	
		if ($pxIds) {			
			$pxItems = $this->pxitems->listAll(" del = 0 AND px_id in (".implode(',',$pxIds).")", "item_id desc");
		}
		if ($pxItems) {
			foreach ($pxItems as $v) {
				foreach ($data as $k => $px) {
					if ($v['px_id'] == $px['id']) {
						$data[$k]['items'][] = $v['item_id'];
					}
				}
				$prodIds[] = $v['item_id'];
			}
		}
		//获取单品信息
		$prodData = $this->myitem->getItemByIds($prodIds);		
		foreach($prodData as $ip=>$vp)
		{
			$prodData[$ip]['img_url'] = IMAGE_DOMAIN.$prodData[$ip]['img_url'];
		}
		foreach ($data as $k => $px) {
			$num = 0;
			foreach ($px['items'] as $dp_k => $id) {
				if (!isset($prodData[$id]))	{
					continue;
				}
				$data[$k]['dp'][] = $prodData[$id];
				
				$num ++;
				if ($num >= 3) {
					break;
				}
			}
			
		}
		
		echo $this->customJsonEncode($data);
		exit;
	}
		
		
	/* id为pinxiu id */
	/**
	 * 搭配的详情页面
	 * id		搭配的ID
	 */
	public function detailAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam('id');
		if (!$id)
		{
			echo "";
			exit;
		}


		$where['id'] = $id;
		$where['status'] = 1;
		$fields="id,uid,head_pic,px_pic,title,occasion,description,tags";
		//获取搭配信息
		
		$pinxiu = $this->pinxiu->listPageWithFields($fields,$where);
		if(!$pinxiu){
			exit;
		}
		$pxInfo = $pinxiu[0];
		
		$pxInfo['head_pic'] = IMAGE_DOMAIN.$pxInfo['head_pic']; 
		$pxInfo['px_pic'] = IMAGE_DOMAIN.$pxInfo['px_pic'];
		
		//获取搭配的单品
		$pxIds = $prodIds = $userIds = $brandIds = array();
		$userIds[] = $pxInfo['uid'];
	
		$fieldsFields="id,uid,title,price,img_url";
		$pageSize = $showPageSize = 6;
		$pxItems = $this->pxitems->listAll(array('px_id' => $id , 'del' => 0) , 'id desc');

		foreach ($pxItems as $item){
			$prodIds[] = $item['item_id'];
		}
		unset($item);
	
		//获取单品信息
		$prodData = $this->myitem->getItemByIds($prodIds);
		foreach($prodData as $i=>$prodDataDetail)
		{
			$prodData[$i]['img_url']=IMAGE_DOMAIN.$prodDataDetail['img_url'];
		}


		$fieldsUser = "id, username, head_pic, sex";
		//获取用户信息
		$userData = $this->user->listPageWithFields($fieldsUser,array('id'=>$userIds[0]));
		
		$pxInfo['user'] = $userData[0];
		$pxInfo['items'] = $prodData;
		
		echo $this->customJsonEncode($pxInfo);
		
		exit;
	}

	
	
	/**
	 * 获取搭配的详情信息[搭配里的单品大小，位置坐标]
	 * pxid			搭配的ID
	 */
	public function pxinfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		global $BUY_URL,$TRYOUT_IMG_URL;
	
		$pxid = $this->getIntParam("pxid");
		$pxArr = array();
		$fieldsPinxiu = "";
		
		$fieldsPinxiu = "id,uid,username,head_pic,px_pic,title,occasion,likenum";
		$row = $this->pinxiu->getRowWithFields($fieldsPinxiu,$pxid);
		if(!$row){
			exit;
		}
		$row['head_pic'] = IMAGE_DOMAIN.$row['head_pic'];
		$row['px_pic'] = IMAGE_DOMAIN.$row['px_pic'];
		
		if($row){
			$row["px_pic_320"] = $TRYOUT_IMG_URL.getPropath($row['px_pic'],320);
			$pxArr["pinxiu"] = $row;
			$fieldsPxitems = "id,px_id,type,item_id,height,width,img_url,d_price,pos_top,pos_left,bg_status,z_index,v_flip,h_flip,del";
			$pxitemlist = $this->pxitems->listAllWithFields($fieldsPxitems,array('px_id' => $pxid,'del' => 0));
			if($pxitemlist){
				$arr_itemid = array();
				foreach ($pxitemlist as $key_0 => $value_0){
					$arr_itemid[] = $value_0["item_id"];
				}
				$data = $this->myitem->getItemByIds($arr_itemid);
				foreach ($data as $key => $value){
					$data[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
					$data[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
					$data[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
					$data[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
					$data[$key]['width'] = 200;
					$data[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
					foreach ($pxitemlist as $key1 => $value1){
						if($value1["item_id"] == $value["id"]){
							$data[$key]["px_img_url"] = $TRYOUT_IMG_URL.$value1["img_url"];
							$data[$key]["px_type"] = $value1["type"];
							$data[$key]["px_height"] = $value1["height"];
							$data[$key]["px_width"] = $value1["width"];
							$data[$key]["px_pos_top"] = $value1["pos_top"];
							$data[$key]["px_pos_left"] = $value1["pos_left"];
							$data[$key]["px_bg_status"] = $value1["bg_status"];
							$data[$key]["px_v_flip"] = $value1["v_flip"];
							$data[$key]["px_h_flip"] = $value1["h_flip"];
							$data[$key]["px_z_index"] = $value1["z_index"];
							$data[$key]["px_del"] = $value1["del"];
						}
					}
				}
				$arr = array();
				foreach ($data as $key_2 => $value_2){
					$arr[] = $value_2;
				}
				$pxArr["pxitems"] = $arr;
				
				echo $this->customJsonEncode($pxArr);exit();
			}else{
				echo "";exit();
			}
		}else{
			echo "";exit();
		}
	}
	
	
	/**
	 * 获取推荐的搭配列表[置顶的搭配]
	 * page			页码
	 */
	public function pxtoplistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		global $BUY_URL,$TRYOUT_IMG_URL;
	
		$where[] = "istop=1";
		$order = "id desc";
		$count		= $this->pinxiu->listCount($where);
		$pageSize	= 12;
		$pagecount = ceil($count/$pageSize);
		$page = $this->getIntParam("page");
		if($pagecount < $page){
			echo "";exit;
		}
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$fieldsPinxiu = "id,uid,username,head_pic,px_pic,title,occasion,likenum";
		$data	= $this->pinxiu->listPageWithFields($fieldsPinxiu,$where, $order , $pageSize, $pageId);
	
		foreach ($data as $key => $value){
			$data[$key]["head_pic"] = IMAGE_DOMAIN.$value["head_pic"];
			$data[$key]["px_pic"] = IMAGE_DOMAIN.$value["px_pic"];
		}
		
		echo $this->customJsonEncode($data);
	
	
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
Config::extend('PxController', 'Controller');