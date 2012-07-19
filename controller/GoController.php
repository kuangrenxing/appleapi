<?php
Globals::requireClass('Controller');
Globals::requireTable('User');
Globals::requireTable('Myitem');
Globals::requireTable('Product');

class GoController extends Controller
{
	protected $user;
	protected $myitem;
	protected $product;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->user  		= new UserTable($config);
		$this->myitem		= new MyitemTable($config);
		$this->product		= new ProductTable($config);
	}
	
	public function indexAction()
	{
		$this->config['layoutEnabled'] = false;
		
		$pid = $this->getIntParam('id');
		if (!$pid)
			$this->redirect('/');
		
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,cat_1,cat_2,cat_3,bid,pid,type,tb_fav,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,tags,color,site_name,summary,del,flag,rank,view,favor,likenum,commnum,xjbnum,lxdnum,dpdnum,zfnum";
		$prodInfo = $this->myitem->getRowWithFields($fieldsMyitem, $pid);
		
		$userInfo = $clickData = array();
		if (!$prodInfo){
			unset($prodInfo);
			$prodInfo = $this->product->getRow($pid);
			
			if (!$prodInfo){
				$this->redirect('/');
			}
			$this->view->click = "/click/?pd=".$prodInfo['id'];
			$this->view->link  = "./?m=prod&a=detail&id=".$prodInfo['id'];
			$this->view->type  = "prod";
			
			$clickData['pid'] = $prodInfo['id'];
			$clickData['bid'] = $prodInfo['bid'];
		}else{
			$userInfo = $this->user->getRow($prodInfo['uid']);
			$userInfo['head'] = IMAGE_DOMAIN.getUserPath($userInfo['head_pic'] , 36);
			$this->view->click = "/click/?mt=".$prodInfo['id'];
			$this->view->link  = "./?m=mt&a=detail&id=".$prodInfo['id'];
			$this->view->type  = "mt";
			
			$clickData['myid'] = $prodInfo['id'];
			$clickData['bid'] = $prodInfo['bid'];
		}
	
		if (empty($prodInfo['source_site_url']) || trim($prodInfo['source_site_url']) == ''){
			$this->redirect('/');
		}
		
		$this->view->user = $userInfo;
		$this->view->data = $prodInfo;
		
		//添加统计
		$ip 	= Globals::getClientIp();
		$http	= $_SERVER["HTTP_USER_AGENT"];
		
		//过滤爬虫
		$agent	= strtolower($http);
  		$is_spider = false;
  		
  		/*$spiderArray = array(
  		'googlebot',
  		'sogou web spider',
  		'sogou push spider',
  		'baiduspider',
  		'sosospider',
  		'msnbot',
  		'yandexbot',
  		'mediapartners-google',
  		'youdaobot',
  		'yandex',
  		'mj12bot',
  		'bingbot',
  		'yahoo! slurp',
  		'ia_archiver',
  		'sitebot',
  		'dotbot',
  		'sogou spider',
  		'iaarchiver',
  		'iaskspider',
  		'sosospider',
  		'naverrobot',
  		);
  		
  		foreach ($spiderArray as $sa)
  		{
  			if (strpos($agent , $sa) > -1)
  				$is_spider = true;
  		}
  		
  		if (!$is_spider)
  		{*/
  			$redirectUrl 	= "";
  			$isTaobao 		= false;
  			$clickData['domain']  		= $this->getDomain($prodInfo['source_site_url']);
  			$clickData['clickdepth'] 	= 1;  //到最后的跳出页的距离 
			
  			if (!empty($prodInfo['source_site_url']) && (strpos($prodInfo['source_site_url'] , "taobao.com") !== false || strpos($prodInfo['source_site_url'] , "tmall.com") !== false)){

  				if (strpos($prodInfo['source_site_url'] , "taobao.com") !== false)
  					$clickData['domain']  = "taobao.com";
  				elseif (strpos($prodInfo['source_site_url'] , "tmall.com") !== false)
  					$clickData['domain']  = "tmall.com";
	  			$clickData['clickdepth'] = 1;  //到最后的跳出页的距离 
	  			$isTaobao = true;
	  			$redirectUrl = $prodInfo['source_site_url'];
	  						
	  			//淘宝客监测链接 --- start
	  			preg_match('/(&|\?)id=[^&]+/', $prodInfo['source_site_url'] , $matchs);
	  			if (count($matchs) > 0){
	  				preg_match("/\d+/", $matchs[0] , $tbIds);
	  				if (count($tbIds) > 0){
	  					$taobaoID = $tbIds[0];

	  					//处理成淘宝客链接
	  					Globals::requireClass('Taoke');
	  					$taoke = new Taoke();
	  					$taoke->appKey = '12472693';
	  					$taoke->appSecret = 'e0df4fef1eb639a08d57429c262ce0be';
	  					$taoke->tbkID = '30683698';
	  						
	  					$tbkClickUrl = $taoke->getTBkUrl($taobaoID);
	  						
	  					if ($tbkClickUrl != false){
	  						$redirectUrl = $tbkClickUrl;
	  					}else{
	  						if (strpos($prodInfo['source_site_url'] , "taobao.com") !== false)
  								$redirectUrl = "http://a.m.taobao.com/i".$taobaoID.".htm";
  							elseif (strpos($prodInfo['source_site_url'] , "tmall.com") !== false)
  								$redirectUrl = "http://a.m.taobao.com/i".$taobaoID.".htm";
	  					}
	  				}
	  			}else{
	  				preg_match('/(&|\&)default_item_id=[^&]+/', $prodInfo['source_site_url'] , $matchs);
		  			if (count($matchs) > 0){
		  				preg_match("/\d+/", $matchs[0] , $tbIds);
		  				if (count($tbIds) > 0){
		  					$taobaoID = $tbIds[0];
	
		  					//处理成淘宝客链接
		  					Globals::requireClass('Taoke');
		  					$taoke = new Taoke();
		  					$taoke->appKey = '12472693';
		  					$taoke->appSecret = 'e0df4fef1eb639a08d57429c262ce0be';
		  					$taoke->tbkID = '30683698';
		  						
		  					$tbkClickUrl = $taoke->getTBkUrl($taobaoID);
		  						
		  					if ($tbkClickUrl != false){
		  						$redirectUrl = $tbkClickUrl;
		  					}else{
		  						if (strpos($prodInfo['source_site_url'] , "taobao.com") !== false)
	  								$redirectUrl = "http://a.m.taobao.com/i".$taobaoID.".htm";
	  							elseif (strpos($prodInfo['source_site_url'] , "tmall.com") !== false)
	  								$redirectUrl = "http://a.m.taobao.com/i".$taobaoID.".htm";
		  					}
		  				}
		  			}
	  			}
	  			//---end---
	  			
  			}elseif (!empty($prodInfo['source_site_url']) && (strpos($prodInfo['source_site_url'] , "vancl.com") !== false)){
  				//凡客
  				$clickData['domain']  = "vancl.com";
	  			$clickData['clickdepth'] = 1;  //到最后的跳出页的距离 
	  			$isTaobao = true;
  				$prodInfo['source_site_url'] = str_replace("http://item.vancl.com/","",strtolower($prodInfo['source_site_url']));
  				$vancl_id = substr($prodInfo['source_site_url'],0,stripos($prodInfo['source_site_url'],".html"));
  				$redirectUrl = "http://m.vancl.com/style/StyleHome/0/$vancl_id/0/.mvc?guid=8a4ed2012f074552ae58f2cd19c09ab1";
  			}

  			Globals::requireTable('Click');
  			$click = new ClickTable($this->config);
  			
  			$clickData['ip'] 	= $ip;
  			$clickData['http'] 	= $http;
  			$clickData['createtime'] = date('Y-m-d H:i:s');
  			$id = $click->add($clickData , true);
  			
  			if ($isTaobao && "" != $redirectUrl)
  				$this->redirect($redirectUrl);
  		//}
	}
	
	protected function getDomain($url = '')
	{
		if (is_null($url) || empty($url)){
			$siteHost = "";
		}else{
			if(preg_match_all('#https?://(.*?)($|/)#m', $url , $siteInfo)){
				$siteHost 	= $siteInfo[1][0];
			}else{
				$siteInfo  	= @parse_url($url);
				$siteHost	= $siteInfo['host'];
			}
		}
		
		return $siteHost;
	}
	
	protected function out()
	{
		$this->layout->nav		= 'go';
		parent::out();
	}
}

Config::extend('GoController', 'Controller');