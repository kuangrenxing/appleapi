<?php
Globals::requireClass('Controller');
Globals::requireTable('Pic');
Globals::requireTable('Piccomm');
Globals::requireTable('Pinxiu');
Globals::requireTable('User');
Globals::requireTable('Userstat');
Globals::requireTable('Tweet');
Globals::requireTable('Usermsg');
Globals::requireTable('PicRelation');
Globals::requireTable('PicEvaluate');
Globals::requireTable('PicStarEvaluate');
Globals::requireTable('Star');
Globals::requireTable('Brand');
Globals::requireTable('Myitem');

class PicController extends Controller
{
	protected $pinxiu;
	protected $user;
	protected $userstat;
	protected $tweet;
	protected $usermsg;
	protected $pic;
	protected $piccomm;
	protected $pic_relation;
	protected $brand;
	protected $myitem;
	protected $pic_evaluate;
	protected $pic_star_evaluate;
	protected $star;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->pinxiu			 = new PinxiuTable($config);
		$this->user				 = new UserTable($config);
		$this->userstat			 = new UserstatTable($config);
		$this->tweet			 = new TweetTable($config);
		$this->usermsg			 = new UsermsgTable($config);
		$this->pic				 = new PicTable($config);
		$this->piccomm			 = new PiccommTable($config);
		$this->pic_relation 	 = new PicRelationTable($config);
		$this->brand 			 = new BrandTable($config);
		$this->myitem 			 = new MyitemTable($config);
		$this->pic_evaluate 	 = new PicEvaluateTable($config);
		$this->pic_star_evaluate = new PicStarEvaluateTable($config);
		$this->star 			 = new StarTable($config);
	}
	
	/**
	 * 街拍列表
	 * page			页数
	 */
	public function piclistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$where = array('del' => 0 );
		
		$fieldsPic="id,uid,category,cate,gender,tweet,img_url,ow,oh,tags,likenum,gradenum,gpnum";
		$pageSize = 18;
		$order = "id desc";
		$count = $this->pic->listCount($where);
		$this->view->paging = $this->getPaging($count , $pageSize , $pageId);
		$data = $this->pic->listPageWithFields($fieldsPic,$where , $order , $pageSize , $pageId);
		
		$arr_userid = array();
		foreach ($data as $key_userid => $value_userid){
			$arr_userid[] = $value_userid["uid"];
		}
		$userid 	= '';
		$arr_userid 	= array_unique($arr_userid);
		$userid 	= implode(',' , $arr_userid);
		$userid   	= trim($userid , ',');
		$fieldsUser = "id,username,head_pic,email,sex,city";		
		$userlist = $this->user->listAllWithFields($fieldsUser,"id in ($userid)","id desc");
		unset($arr_userid);
		unset($userid);
		
		foreach ($data as $key => $value){
			$data[$key]["img_url"] = IMAGE_DOMAIN.$value["img_url"];
			foreach ($userlist as $key_user => $value_user){
				if($value["uid"] == $value_user["id"]){
					$value_user["head_pic"] = IMAGE_DOMAIN.$value_user["head_pic"];
					$data[$key]["userinfo"] = $value_user;
				}
			}
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 街拍的详情
	 * picid		街拍ID
	 * ?m=pic&a=picinfo&picid=2
	 */
	public function picinfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$picid = $this->getIntParam("picid");
		$fieldsPic="id,uid,category,cate,gender,tweet,img_url,ow,oh,tags,likenum,gradenum,gpnum";
		$picinfo = $this->pic->getRowWithFields($fieldsPic,$picid);
		
		if($picinfo){
			$picinfo["img_url"] = IMAGE_DOMAIN.$picinfo["img_url"];
			
			$fieldsUser = "id,username,head_pic,email,sex,city";			
			$userinfo = $this->user->getRowWithFields($fieldsUser,$picinfo["uid"]);
			$userinfo["head_pic"] = IMAGE_DOMAIN.$userinfo["head_pic"];
			$picinfo["userinfo"] = $userinfo;
			
			$where 		= array('picid' => $picinfo["id"] , 'zf_id' => 0 , 'pl_id' => 0);
			$fieldsPiccomm = "id,picid,uid,username,head_pic,comment,time_created";
			$piccomm	= $this->piccomm->listAllWithFields($fieldsPiccomm,$where, 'id desc');
			foreach($piccomm as $ip=>$vp)
			{
				$piccomm[$ip]['head_pic'] = IMAGE_DOMAIN.$piccomm[$ip]['head_pic'];
			}
			$picinfo["piccomm"] = $piccomm;
			
			echo $this->customJsonEncode($picinfo);
		}else{
			echo "0";exit;
		}
		
	}
	
	/**
	 * 获取用户上传街拍的明星分类
	 */
	public function pictypeAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		$pictype = array(
			"5"=>"国内明星",
			"6"=>"日韩明星",
			"7"=>"欧美明星",
			"11"=>"时尚街拍",
			"8"=>"商业名人",
			"9"=>"达人红人",
			"4"=>"原创作品",
			"10"=>"其他"
		);
		echo $this->customJsonEncode($pictype);
	}
	
	/**
	 * 用户上传街拍
	 */
	public function addpicAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		Globals::requireClass('UploadFile');
		$upload = new UploadFile();
		$upload->allowExts = array("jpg" , "png" , "gif" , "jpeg" , "JPG" , "PNG" , "GIF" , "JPEG");
		$upDir = "./img/uimg/";
		
		$monDir = $upDir.date("Ym");
		if(!is_dir($monDir)){
			mkdir($monDir , 0777);
		}
		$dayDir = $monDir."/".date("d");
		if(!is_dir($dayDir)){
			mkdir($dayDir , 0777);
		}
		$hourDir = $dayDir."/".date("g");
		if(!is_dir($hourDir)){
			mkdir($hourDir , 0777);
		}
		$upload->savePath = $hourDir."/";
		
		//生成缩略图
		$upload->thumb = true;
		/*$upload->thumbMaxHeight = "300,200,90";
		$upload->thumbMaxWidth = "300,200,90";
		$upload->thumbSuffix = '_300,_200,_90';
		$upload->thumbMode = '3,0,1';*/
		$upload->thumbMaxHeight = "600,300,200,90";
		$upload->thumbMaxWidth = "600,300,200,90";
		$upload->thumbSuffix = '_600,_300,_200,_90';
		$upload->thumbMode = '3,3,0,1';
		
			
		if ($upload->upload()){
			$fileInfo = $upload->getUploadFileInfo();
			$form['pic'] = $fileInfo[0]['savepath'].$fileInfo[0]['savename'];
			$picArr = explode("." , $fileInfo[0]['savename']);
			$size = getimagesize(IMAGE_DOMAIN.$form['pic']);
			$s_str = $size[0]>$size[1]?'width':'height';
			$msg	= $form['pic'];
		}else {
			$msg = "0;;".$upload->getErrorMsg();
			exit;
		}
		$params = $this->getParams('uid,title,type');
		
		$newP['title'] = trim($params['title']);
		$newP['tweet'] = htmldecode($params['title']);
		$newP['img_url'] = $msg;
		$newP['uid'] = $params['uid'];
		$newP['likenum'] = rand(20 , 40);
		$newP['time_created'] = time();
		$newP['del'] = 1;
		$newP['category'] = $params['type'];/*取明星所属分类的值插入到表tb_pic中的cate字段--2012年2月20日17:22:02 yhm*/	
		//获取原始图片宽高度
		if($newP['img_url']){
			$size = @getimagesize("http://image.tuolar.com/".$newP['img_url']);
			$newP['ow'] = $size[0];
			$newP['oh'] = $size[1];
		}
		
		$new_id = $this->pic->insert($newP , true);
		echo $new_id;
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

Config::extend('PicController', 'Controller');
