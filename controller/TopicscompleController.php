<?php
Globals::requireClass('Controller');
Globals::requireTable('Myitem');
Globals::requireTable('User');
Globals::requireTable('Prodcomm');

class TopicscompleController extends Controller
{
	protected $myitem;
	protected $user;
	protected $prodcomm;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->myitem		= new MyitemTable($config);
		$this->user  		= new UserTable($config);
		$this->prodcomm		= new ProdcommTable($config);
	}
	
	/*
	 * 12专题控分类的列表
	 * type			1[女衣服],2[女裤子],3[女裙子],4[女包包],5[女鞋子],6[女配饰],7[女内衣],8[男衣服],9[男鞋],10[男皮包],11[男配饰],12[童装]
	 * page			列表分页页码[默认：1]
	 */
	public function tclistAction()
	{
		global $TRYOUT_IMG_URL,$BUY_URL;
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('type,page');
		$pageId = trim($params['page']);
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		$type = trim($params['type']);
		if(!$type)
		{
			exit;
		}		
		if(empty($type) || trim($type) == '' || $type === 0){
			$type = 1;
		}
		if($type == 1){
			$cat = 11;
		}elseif($type == 2){
			$cat = 12;
		}elseif($type == 3){
			$cat = 13;
		}elseif($type == 4){
			$cat = 14;
		}elseif($type == 5){
			$cat = 16;
		}elseif($type == 6){
			$cat = 17;
		}elseif($type == 7){
			$cat = 18;
		}elseif($type == 8){
			$cat = 21;
		}elseif($type == 9){
			$cat = 24;
		}elseif($type == 10){
			$cat = 23;
		}elseif($type == 11){
			$cat = 25;
		}else{
			$cat = 4;
		}
		$catid = -1;
		if($cat){
			$len = strlen($cat);
			$catid = intval($cat);
			if($len == 1){
				$findAr['l_cat'] = $catid*1000;
				$findAr['s_cat'] = ($catid+1)*1000;
			}elseif($len == 2){
				$findAr['l_cat'] = $catid*100;
				if($cat == 21){
					$findAr['s_cat'] = ($catid+2)*100;
				}else{
					$findAr['s_cat'] = ($catid+1)*100;
				}
			}elseif($len > 2){
				$findAr['cat'] = $catid;
			}
		}
		
		if(isset($findAr['cat'])){
			$where[] = "cat_1=".$findAr['cat'];
		}
		if(isset($findAr['l_cat']) && isset($findAr['s_cat'])){
			$where[] = "(cat_1>=".$findAr['l_cat']." and cat_1<=".$findAr['s_cat'].")";
		}
		
//		$where[] = "source_site_url <> ''";
		$where[] = "del=0";
//		$where[] = "img_url <> ''";
//		$where[] = "title <> ''";
		$order = "id desc";
		
		$pageSize	= 18;
		if($pageId == 0){
			$order = "id desc";
		}
		
		$count		= $this->myitem->listCount($where);
		
		if($count > 0){
			if($pageId > ceil($count/$pageSize)){
				echo "";
				return;
			}
		}else{
			echo "";
			return;
		}
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
		
		$data	= $this->myitem->listPageWithFields($fieldsMyitem,$where, $order ,$pageSize, $pageId);
		//$clist = array();
		//$i = 0;
		foreach($data as $key => $value){
			//if(@fopen($TRYOUT_IMG_URL.$value['img_url'],'r')){
				//$clist[$i]["id"] = $value["id"];
				//$clist[$i]["title"] = $value["title"];
				//$clist[$i]["price"] = $value["price"];
				//$clist[$i]["summary"] = $value["summary"];
				//$clist[$i]["likenum"] = $value["likenum"];
				//$clist[$i]["source_site_url"] = $value["source_site_url"];
				$data[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
				
				$data[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
				
				$data[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
				$data[$key]['img_url'] = IMAGE_DOMAIN.$data[$key]['img_url'];
				
				/*
				$img_url200 = $data[$key]['img_url_200'];
				$img_wh = array();
				$img_wh = getimagesize($img_url200);
				$data[$key]['width'] = $img_wh[0];
				$data[$key]['height'] = $img_wh[1];
				
				if(!isset($value["summary"])){
					$data[$key]["summary"] = '';
				}
				unset($img_wh);
				*/
				$data[$key]['width'] = 200;
				$data[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
				//$i++;
			//}
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/*
	 * 获取专题控的信息
	 * id			单品ID
	 * page			页码[默认:0或者1]
	 */
	public function tcinfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$myitem = array();
		
		$params = $this->getParams('id,page');
		$myid = $params["id"];
		if (!$myid){
			echo "";
			return;
		}
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,time_created,source_img_url,summary,favor,likenum,commnum";
		$rowMyitem = $this->myitem->getRowWithFields($fieldsMyitem,$myid);
		if (!$rowMyitem){
			echo "";
			return;
		}
		//$myitem["myitem"] = array('id'=>$rowMyitem["id"],'uid'=>$rowMyitem["uid"],'title'=>$rowMyitem["title"],'price'=>$rowMyitem["price"],'img_url'=>IMAGE_DOMAIN.$rowMyitem["img_url"]);
		$rowMyitem["img_url"] = IMAGE_DOMAIN.$rowMyitem["img_url"];
		$rowMyitem["source_site_url"] = SOURCE_DOMAIN."?m=go&id=".$rowMyitem["id"];
		$rowMyitem["source_url"] = SOURCE_DOMAIN."?m=go&id=".$rowMyitem["id"];
		$rowMyitem["time_created"] = date("Y-m-d H:i:s",$rowMyitem["time_created"]);
		if(!isset($rowMyitem["summary"])){
			$rowMyitem["summary"] = '';
		}
		
		$myitem["myitem"] = $rowMyitem;
		$fieldsUser = "id,username,head_pic,email,sex,time_created";
		$userInfo = $this->user->getRowWithFields($fieldsUser,$rowMyitem['uid']);
		$userInfo['link'] = "./?m=user&uid=".$rowMyitem['uid'];
		$userInfo['head'] = IMAGE_DOMAIN.getUserPath($userInfo['head_pic'] , 36);
		$userInfo['head_pic'] = IMAGE_DOMAIN.$userInfo['head_pic'];
		$userInfo["time_created"] = date("Y-m-d H:i:s",$userInfo["time_created"]);
		$userInfo['link'] = TUOLAR_DOMAIN.$userInfo['link'];
		$myitem["userinfo"] = $userInfo;
		
		//更新浏览数
    	$this->myitem->update(array("view=view+".rand(10,30)) , $rowMyitem['id']);
    	
		$pageId = $params['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		$where 		= array('item_id' => $myid , 'zf_id' => 0 , 'pl_id' => 0);
		$count		= $this->prodcomm->listCount($where);
		$pageSize	= 18;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		
		$fieldsProdcomm = "id,prod_id,item_id,uid,username,head_pic,comment,time_created";
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
				$data[$keyd]['head_pic'] = IMAGE_DOMAIN.$data[$keyd]['head_pic'];
				$data[$keyd]["userinfo"] = $pusinfo;
				$data[$keyd]["time_created"] = date("Y-m-d H:i:s",$valued["time_created"]);
				unset($pusinfo);
			}
			unset($userList);
		}
		
		$myitem["prodcomm"] = $data;
		
		echo $this->customJsonEncode($myitem);
	}
	
	/**
	 * 12分类的信息
	 */
	public function tlistAction(){
		global $TC_IMAGE;
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$tlist = array(
			array('id'=>1,'title'=>'女衣服','writ_url_160'=>$TC_IMAGE.'/images/nvyifu_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvyifu_img_160.png','img_url_80'=>'','paixu'=>'1'),
			array('id'=>2,'title'=>'女裤子','writ_url_160'=>$TC_IMAGE.'/images/nvkuzi_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvkuzi_img_160.png','img_url_80'=>'','paixu'=>'2'),
			array('id'=>3,'title'=>'女裙子','writ_url_160'=>$TC_IMAGE.'/images/nvqunzi_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvqunzi_img_160.png','img_url_80'=>'','paixu'=>'3'),
			array('id'=>4,'title'=>'女包包','writ_url_160'=>$TC_IMAGE.'/images/nvbaobao_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvbaobao_img_160.png','img_url_80'=>'','paixu'=>'4'),
			array('id'=>5,'title'=>'女鞋子','writ_url_160'=>$TC_IMAGE.'/images/nvxiezi_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvxiezi_img_160.png','img_url_80'=>'','paixu'=>'5'),
			array('id'=>6,'title'=>'女配饰','writ_url_160'=>$TC_IMAGE.'/images/nvpeishi_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvpeishi_img_160.png','img_url_80'=>'','paixu'=>'6'),
			array('id'=>7,'title'=>'女内衣','writ_url_160'=>$TC_IMAGE.'/images/nvneiyi_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nvneiyi_img_160.png','img_url_80'=>'','paixu'=>'7'),
			array('id'=>8,'title'=>'男衣服','writ_url_160'=>$TC_IMAGE.'/images/nanyifu_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nanyifu_img_160.png','img_url_80'=>'','paixu'=>'8'),
			array('id'=>9,'title'=>'男鞋','writ_url_160'=>$TC_IMAGE.'/images/nanxie_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nanxie_img_160.png','img_url_80'=>'','paixu'=>'9'),
			array('id'=>10,'title'=>'男皮包','writ_url_160'=>$TC_IMAGE.'/images/nanpibao_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nanpibao_img_160.png','img_url_80'=>'','paixu'=>'10'),
			array('id'=>11,'title'=>'男配饰','writ_url_160'=>$TC_IMAGE.'/images/nanpeishi_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/nanpeishi_img_160.png','img_url_80'=>'','paixu'=>'11'),
			array('id'=>12,'title'=>'童装','writ_url_160'=>$TC_IMAGE.'/images/tongzhuang_writ_160.png','writ_url_80'=>'','img_url_160'=>$TC_IMAGE.'/images/tongzhuang_img_160.png','img_url_80'=>'','paixu'=>'12')
		);
		
		echo $this->customJsonEncode($tlist);
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

Config::extend('TopicscompleController', 'Controller');
