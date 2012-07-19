<?php
Globals::requireClass('Controller');
Globals::requireTable('AppVersion');
Globals::requireTable('EverydayLog');
Globals::requireTable('AppFeedback');
Globals::requireTable('CityWeather');
	  
class AppController extends Controller
{
	protected $appVersion;
	protected $everydayLog;
	protected $appFeedback;
	protected $cityWeather;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->appVersion = new AppVersionTable($config);
		$this->everydayLog = new EverydayLogTable($config);
		$this->appFeedback = new AppFeedbackTable($config);
		$this->cityWeather = new CityWeatherTable($config);
		
	}
	
	public function indexAction()
	{
		
	}
	
	/**
	 * 启动服务,获取版本号等信息
	 * appid		应用的ID
	 */
	public function appversionAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$appid = $this->getIntParam("appid");
		$appInfo = $this->appVersion->getRow(array('appid' => $appid));
		if($appInfo){
			echo $this->customJsonEncode($appInfo);
		}else{
			echo "";
		}
		exit;
	}
	
	/**
	 * 应用的每天打开次数
	 * appid			app应用的ID
	 */
	public function applogAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$appid = $this->getIntParam("appid");
		$currtime = mktime(0 , 0 , 0 , date('m') , date('d') , date('Y'));
		$appLog = $this->everydayLog->listAll(array('appid' => $appid,'log_time' => $currtime));
		if($appLog){
			$id = $appLog[0]["id"];
			$logNumber = $appLog[0]["log_number"];
			$params["log_number"] = $logNumber+1;
			$this->everydayLog->update($params , $id);
			$appInfo["id"] = $id;
			$appInfo["number"] = $logNumber+1;
			echo $this->customJsonEncode($appInfo);
		}else{
			$params['appid'] = $appid;
			$params['log_number'] = 1;
			$params['log_time'] = $currtime;
			$params['createtime'] = time();
			$id = $this->everydayLog->add($params , true);
			$appInfo["id"] = $id;
			$appInfo["number"] = 1;
			echo $this->customJsonEncode($appInfo);
		}
		exit;
	}
	
	/**
	 * 提交用户反馈
	 * appid			app应用的ID
	 * fbname			用户名称
	 * fbemail			用户邮箱
	 * fbphone			用户手机
	 * fbtext			反馈内容
	 */
	public function feedbackAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('appid,fbname,fbemail,fbphone,fbtext');
		
		if(empty($params['appid']) || trim($params['appid']) == ''){
			echo "";
			exit;
		}
		$params["fbname"] = iconv("GB2312","UTF-8",$params["fbname"]);
		$params["fbemail"] = iconv("GB2312","UTF-8",$params["fbemail"]);
		$params["fbphone"] = iconv("GB2312","UTF-8",$params["fbphone"]);
		$params["fbtext"] = iconv("GB2312","UTF-8",$params["fbtext"]);
		$params["createtime"] = time();
		$fbid = $this->appFeedback->add($params , true);
		if($fbid)
			echo $this->customJsonEncode($fbid);
		else
			echo "";
		exit;
	}
	
	/**
	 * 获取一个地点的一周天气
	 * wid			城市的编号
	 * type			数据类型[1->实时气温,2->实时风向,3->7天预报,4->所有指数]
	 */
	public function cityweatherAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('wid,type');
		
		require("./cache/weather/".$params['wid'].".php");
		$update_arr = array();
		$data = array();
		if($params['type'] == '1'){
			if($oneday_weather == '0'){
				$data = $this->cityWeather->getRow(array('city_id' => $params['wid']));
				if($data){
					$oneday_weather = $data["oneday_weather"];
				}
				unset($data);			
			}
			echo $oneday_weather;
		}else if($params['type'] == '2'){
			if($threedays_weather == '0'){
				$data = $this->cityWeather->getRow(array('city_id' => $params['wid']));
				if($data){
					$threedays_weather = $data["threedays_weather"];
				}
				unset($data);
			}
			echo $threedays_weather;
		}else if($params['type'] == '3'){
			if($sevendays_weather == '0'){
				$data = $this->cityWeather->getRow(array('city_id' => $params['wid']));
				if($data){
					$sevendays_weather = $data["sevendays_weather"];
				}
				unset($data);
			}
			echo $sevendays_weather;
		}else if($params['type'] == '4'){
			if($zs == '0'){
				$data = $this->cityWeather->getRow(array('city_id' => $params['wid']));
				if($data){
					$zs = $data["zs"];
				}
				unset($data);
			}
			echo $zs;
		}else{
			echo "";
		}
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
Config::extend('AppController', 'Controller');
