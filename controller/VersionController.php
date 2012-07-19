<?php
Globals::requireClass('Controller');
Globals::requireTable('AppVersion');
Globals::requireTable('EverydayLog');

class VersionController extends Controller
{
	
	protected $appVersion;
	protected $everydayLog;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->appVersion		= new AppVersionTable($config);
		$this->everydayLog		= new EverydayLogTable($config);
	}
	
	public function indexAction()
	{
	}
	
	/**
	 * 无线的版本控制
	 * appid			无线APP的ID
	 * vnum				无线APP的版本号
	 */
	public function addversionAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('appid,vnum');
		
		$appV = array();
		$appV = $this->appVersion->getRow(array('appid' => $params["appid"]));
		
		if ($appV){
			if($appV["version_number"] != $params["appid"]){
				echo $this->customJsonEncode($appV);
			}
		}else{
			echo "";
		}
		exit;
	}
	
	/**
	 * app的每一天浏览的总数量
	 * appid			无线APP的ID
	 */
	public function everydaylogAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('appid');
		$currtime = mktime(0 , 0 , 0 , date('m') , date('d') , date('Y'));
		
		$arr = $logArr = array();
		$logArr = $this->everydayLog->getRow(array("appid" => $params["appid"], "log_time" => $currtime));
		if($logArr){
			$id = $logArr["id"];
			$log_number = $logArr["log_number"]+1;
			$result = $this->everydayLog->update(array('log_number' => $log_number) , $id);
			$arr = array("id" => $id,"log_number" => $log_number);
		}else{
			$add_log = array(
				"appid" => $params["appid"],
				"log_number" => 1,
				"log_time" => $currtime,
				"createtime" => time()
			);
			$result = $this->everydayLog->add($add_log,true);
			$arr = array("id" => $result,"log_number" => 1);
		}
		echo $this->customJsonEncode($arr);
		exit;
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

Config::extend('VersionController', 'Controller');
