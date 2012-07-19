<?php
Globals::requireClass('Controller');
Globals::requireTable('AppManage');

class AppmanageController extends Controller
{
	protected $appmanage;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->appmanage		= new AppManageTable($config);
	}
	
	/*
	 * 获取app应用的列表
	 * page			列表分页页码[默认：1]
	 * ?m=appmanage&a=amlist
	 */
	public function amlistAction(){
		global $TRYOUT_IMG_URL;
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$pageId = $this->getIntParam('page');
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0){
			$pageId = 1;
		}else{
			$pageId = $pageId+1;
		}
		$arr_AppManage = array();
		
		$order = "id desc";
		
		$where_emphasis[] = "del=0";
		$where_emphasis[] = "emphasis_img_url <> ''";
		$where_emphasis[] = "emphasis=1";
		
		$fields = "id,title,summary,star,top_img_url,itunes_url,emphasis_img_url,emphasis,time_created";
		$data_emphasis	= $this->appmanage->listAllWithFields($fields, $where_emphasis, $order);
		if(!$data_emphasis)	{
			exit;
		}
		foreach ($data_emphasis as $keye => $valuee){
			$data_emphasis[$keye]["top_img_url"] = $TRYOUT_IMG_URL.$valuee["top_img_url"];
			$data_emphasis[$keye]["emphasis_img_url"] = $TRYOUT_IMG_URL.$valuee["emphasis_img_url"];
		}
		
		$arr_AppManage["emphasis"] = $data_emphasis;
		
		$where[] = "del=0";
		$where[] = "top_img_url <> ''";
		
		$pageSize	= 10;
		if($pageId == 0){
			$order = "id desc";
		}
		
		$count = $this->appmanage->listCount($where);
		
		if($count > 0){
			if($pageId > ceil($count/$pageSize)){
				echo "";
				return;
			}
		}else{
			echo "";
			return;
		}
		
		$data	= $this->appmanage->listPageWithFields($fields, $where, $order ,$pageSize, $pageId);
		if(!$data){
			exit;
		}
		foreach ($data as $keyd => $valued){
			$data[$keyd]["top_img_url"] = $TRYOUT_IMG_URL.$valued["top_img_url"];
			$data[$keyd]["emphasis_img_url"] = $TRYOUT_IMG_URL.$valued["emphasis_img_url"];
		}
		$arr_AppManage["data"] = $data;
		
		echo $this->customJsonEncode($arr_AppManage);
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

Config::extend('AppmanageController', 'Controller');
