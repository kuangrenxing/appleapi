<?php
Globals::requireClass('Controller');
Globals::requireTable('Album');
Globals::requireTable('Itemalbum');
Globals::requireTable('Myitem');
Globals::requireTable('User');

class AlbumController extends Controller
{
	protected $album;
	protected $Itemalbum;
	protected $myitem;
	protected $user;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->album 		= new AlbumTable($config);
		$this->Itemalbum  	= new ItemalbumTable($config);
		$this->myitem 		= new MyitemTable($config);
		$this->user			= new UserTable($config);
	}

	/**
	 * 专辑列表
	 * page		页码
	 * fields 	为要输出字段，可选。
	 */
	public function albumlistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$fields = $this->getParam("fields");
		
		
		$where = array();
		$count 		= $this->album->listCount($where);
		$pageSize 	= 20;
		$pagecount = ceil($count/$pageSize);
		$page = $this->getIntParam("page");
		if($pagecount < $page){
			echo "";exit;
		}
		$order 		= array('id desc');
		$fieldsAlbum="id,title,type,img_url,big_img_url,app_img_url_max,app_img_url_min,alink,content";
		
		$fieldsAlbum = $this->mergeFields($fieldsAlbum, $fields);		
		
		$this->view->paging = $this->getPaging($count , $pageSize , $pageId);
		$data = $this->album->listPageWithFields($fieldsAlbum, $where , $order , $pageSize , $pageId);
		if(!$data){
			exit;
		}
		foreach ($data as $key => $value){
			if(isset($value["img_url"]) && $value["img_url"] != ''){
				$data[$key]["img_url"] = IMAGE_DOMAIN.$value["img_url"];
			}
			if(isset($value["big_img_url"]) && $value["big_img_url"] != ''){
				$data[$key]["big_img_url"] = IMAGE_DOMAIN.$value["big_img_url"];
			}
			if(isset($value["app_img_url_max"]) && $value["app_img_url_max"] != ''){
				$data[$key]["app_img_url_max"] = IMAGE_DOMAIN.$value["app_img_url_max"];
			}
			if(isset($value["app_img_url_min"]) && $value["app_img_url_min"] != ''){
				$data[$key]["app_img_url_min"] = IMAGE_DOMAIN.$value["app_img_url_min"];
			}
		}
		
		echo $this->customJsonEncode($data);
	}
	
	
	/**
	 * 根据专题分类获取专题列表
	 * type			专题分类{1[促销],2[风格],3[爆款],4[流行话题],5[婚庆],6[男士]}
	 * fields 	为要输出字段，可选。
	 */
	public function listAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$type = $this->getIntParam('type');
		$otherfields = $this->getParam("fields");
	
		if(!$type){
			echo "";exit;
		}
	
		$where = array();
		$where = array('type'=>$type,'status'=>1);
	
		$fields="id,title,type,img_url,alink,content";
		$fields = $this->mergeFields($fields, $otherfields);
		$order = "id desc";
		$count = $this->album->listCount($where);
		$pageSize = 25;
	
		$paging = $this->getPaging($count, $pageSize, $pageId, 3);
	
		$datas = $this->album->listPageWithFields($fields,$where, $order, $pageSize, $pageId);
		if(!$datas){
			echo "";
			exit;
		}
		foreach($datas as $i=>$data)
		{
			$datas[$i]['img_url']=IMAGE_DOMAIN.$data['img_url'];
		}
		//转格式
		echo $this->customJsonEncode($datas);
	
		exit;
	
	}
	
	
	/**
	 * 专辑相关的单品
	 * aid			专辑ID
	 * page			页码
	 */
	public function albumitemsAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		global $TRYOUT_IMG_URL,$BUY_URL;
		$aid = $this->getIntParam("aid");
		$data = $this->Itemalbum->listAll(array('album_id'=>$aid));
		if($data){
			$items_arr = array();
			foreach ($data as $key => $value){
				$items_arr[] = $value["item_id"];
			}
			$itemsdata = $this->myitem->getItemByIds($items_arr);
			if(!$itemsdata){
				exit;
			}
			foreach ($itemsdata as $key_item => $value_item){
				$itemsdata[$key_item]["source_site_url"] = $BUY_URL."?m=go&id=".$value_item['id'];
				
				$itemsdata[$key_item]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value_item['img_url'],200);
				
				$itemsdata[$key_item]['img_url_400'] = $TRYOUT_IMG_URL.$value_item['img_url'];
				$itemsdata[$key_item]['width'] = 200;
				$itemsdata[$key_item]['height'] = floor($value_item["oh"]*(200/$value_item["ow"]));
				if(!isset($value_item["summary"])){
					$itemsdata[$key_item]["summary"] = '';
				}
			}
			echo $this->customJsonEncode($itemsdata);
		}else{
			echo "";exit;
		}		
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

Config::extend('AlbumController', 'Controller');
