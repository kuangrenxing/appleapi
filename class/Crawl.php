<?php
//定义抓取函数块
class Crawl
{
	public $html = '';

	function hunt($url)
	{
//		set_time_limit(0);
//		$curl = curl_init();
//		curl_setopt($curl,CURLOPT_URL,$url);
//		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true); // 返回字符串，而非直接输出
//		curl_setopt($curl,CURLOPT_HEADER, false);   // 不返回header部分
//		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT, 120);   // 设置socket连接超时时间
//		$html = curl_exec($curl);
//		curl_close($curl);
		
		set_time_limit(0);
		$curl = curl_init(); //创建一个cURL资源
		curl_setopt($curl,CURLOPT_URL,$url); //设置抓取url
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //文本流形式返回
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)"); 
		curl_setopt($curl, CURLOPT_HEADER, false); //设定是否显示头信息 
		curl_setopt($curl, CURLOPT_NOBODY, false); //设定是否输出页面内容 
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
		$html = curl_exec($curl); //抓取并传递给浏览器
		curl_close($curl); //关闭cURL，释放资源
		
		if (isset($html)){
			$this->html = $html;
			
			//淘宝
			if(preg_match('/.taobao.com/',$url)){
				return self::getTaobao($url);
			}
			
			//淘宝商城
			if(preg_match('/.tmall.com/',$url)){
				return self::getTmall($url);
			}
			
			// 优衣库
			if(preg_match('/.uniqlo.cn/',$url)){
				return self::getUniqlo($html);
			}
			
			//凡客
			if(preg_match('/.vancl.com/',$url)){
				return self::getVancl($html);
			}
			
			//俏物悄语网
			if(preg_match('/.ihush.com/',$url)){
				return self::getHush($html);
			}
			
			//麦包包
			if(preg_match('/.mbaobao.com/',$url)){
				return self::getMbaobao($html);
			}
			
			//麦考林
			if(preg_match('/.m18.com/',$url)){
				return self::getM18($html);
			}
			
			//梦芭莎
			if(preg_match('/.moonbasa.com/',$url)){
				return self::getMoonbasa($html);
			}
			
			//兰缪
			if(preg_match('/.lamiu.com/',$url)){
				return self::getLamiu($html);
			}
			
			//第五大道
			if(preg_match('/.5lux.com/',$url)){
				return self::get5Lux($html);
			}
			
			//钻石小鸟
			if(preg_match('/.zbird.com/',$url)){
				return self::getZbird($html);
			}
			
			//拍拍
			if(preg_match('/.paipai.com/',$url)){
				return self::getPaiPai($html);
			}
			
			//时尚起义
			if(preg_match('/.shishangqiyi.com/',$url)){
				return self::getShiShangQiYi($html);
			}
			
			//时尚71
			if(preg_match('/.shishang71.com/',$url)){
				return self::getShiShang71($html);
			}
			return false;
		}else{
			return false;
		}
	}
	
	//淘宝
	function getTaobao($html = '')
	{
		$taobaoID 	= 0;
		$data 	= array();
		if ($html != ''){
			preg_match('/(&|\?)id=[^&]+/', $html , $matchs);
			if (count($matchs) > 0){
				preg_match("/\d+/", $matchs[0] , $tbIds);
				if (count($tbIds) > 0){
					$taobaoID = $tbIds[0];
				}
			}
		  	
			if ($taobaoID){
				require_once('SDK/taobao/TopSdk.php');
				$c = new TopClient;
				$c->appkey = '12472693';
				$c->secretKey = 'e0df4fef1eb639a08d57429c262ce0be';
				$req = new ItemGetRequest;
				$req->setFields("num_iid,title,price,item_img");
				$req->setNumIid($taobaoID);
				$resp = $c->execute($req);
				foreach ($resp as $row){
					if (property_exists($row , 'title')){
						$prodInfo = array();
						$prodInfo['title'] = $row->title;
						$prodInfo['price'] = $row->price;
//						$data['numid'] = $row->num_iid;
						$prodInfo['img'] = $row->item_imgs->item_img->url;
						$str_prodinfo = $prodInfo['title']."=@+".$prodInfo['price']."=@+".$prodInfo['img'];
						
						$arr_prod = explode("=@+",$str_prodinfo);
						
						$data["title"] = $arr_prod[0];
						$data["price"] = $arr_prod[1];
						$data["img"] = $arr_prod[2];
						unset($arr_prod,$str_prodinfo,$prodInfo);
						return $data;
					}
				}
			}
		}
		return false;
	}
	
	//淘宝商城
	function getTmall($html = '')
	{
		$taobaoID 	= 0;
		$data 	= array();
		if ($html != ''){
			preg_match('/(&|\?)id=[^&]+/', $html , $matchs);
			if (count($matchs) > 0){
				preg_match("/\d+/", $matchs[0] , $tbIds);
				if (count($tbIds) > 0){
					$taobaoID = $tbIds[0];
				}
			}
		  	
			if ($taobaoID){
				require_once('SDK/taobao/TopSdk.php');
				$c = new TopClient;
				$c->appkey = '12472693';
				$c->secretKey = 'e0df4fef1eb639a08d57429c262ce0be';
				$req = new ItemGetRequest;
				$req->setFields("num_iid,title,price,item_img");
				$req->setNumIid($taobaoID);
				$resp = $c->execute($req);
				foreach ($resp as $row){
					if (property_exists($row , 'title')){
						$prodInfo = array();
						$prodInfo['title'] = $row->title;
						$prodInfo['price'] = $row->price;
//						$data['numid'] = $row->num_iid;
						$prodInfo['img'] = $row->item_imgs->item_img->url;
						$str_prodinfo = $prodInfo['title']."=@+".$prodInfo['price']."=@+".$prodInfo['img'];
						
						$arr_prod = explode("=@+",$str_prodinfo);
						
						$data["title"] = $arr_prod[0];
						$data["price"] = $arr_prod[1];
						$data["img"] = $arr_prod[2];
						unset($arr_prod,$str_prodinfo,$prodInfo);
						return $data;
					}
				}
			}
		}
		return false;
	}
	
	//优衣库
	function getUniqlo($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		$title=iconv('GBK','UTF-8',$title[1]);
		$data["title"] = $title;
		//图片
		preg_match('/<img[^>]*id="J_ImgBooth"[^r]*rc=\"([^"]*)\"[^>]*>/', $html, $img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<([a-z]+)[^i]*id=\"J_StrPrice\"[^>]*>([^<]*)<\/\\1>/is', $html, $price);
		if(isset($price[2])){
			$data["price"] = $price[2];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//凡客
	function getVancl($html = '')
	{
		if ('' == $html)
			$html = $this->html;
		
		$data = array();	
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			//$data["title"] = str_replace("-VANCL 凡客诚品","",$title[1]);
			$data["title"] = trim(str_replace("-VANCL 凡客诚品","",$title[1]));
		}else{
			return false;
		}
		//图片
		preg_match('/<img[^>]*[^r]*rc=\"([^"]*)\"[^>]*id="midimg">/', $html, $img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			preg_match('/<img[^r]*id="midimg"[^r]*rc=\"([^"]*)\"[^>]*>/',$html,$img);
			
			if(isset($img[1])){
				$data["img"] = $img[1];
			}else{
				return false;
			}
		}
		//价格
		//preg_match('/<div class=\"cuxiaoPrice\" style=\'([^<]*)\'>([^<]*)<span>([^<]*)<strong>([^<]*)<\/strong><\/span><\/div>/', $html, $price);//richard  修改于20111227
		preg_match('/<div class=\"cuxiaoPrice\" >([^<]*)<span>([^<]*)<strong>([^<]*)<\/strong><\/span><\/div>/', $html, $price);
		if(isset($price[1])){
			//$data["price"] = $price[4];
			$data["price"] = $price[3];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//俏物悄语网
	function getHush($html = '')
	{
		if ('' == $html)
			$html = $this->html;
		
		$data = array();	
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		$title=iconv('GBK','UTF-8',$title[1]);
		if(isset($title)){
			$data["title"] = str_replace("","",$title);
		}else{
			return false;
		}
		//图片
		preg_match('/<div class=\"dw\" id=\"pmg\"><a href="([^<]*)" class=\"jqzoom\"  target=\"aaa\"><img src="([^<]*)"  id=\"pimg\" border=0  \/><\/a><\/div>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<span class=\"price_2\">([^<]*)<\/SPAN>/', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//时尚起义
	function getShiShangQiYi($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/.* &gt;([^<]*)<\/td>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("","",$title[1]);
		}else{
			return false;
		}
		//图片
		preg_match('/<td><img src=\"(.*)\" ><\/td>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = rawurldecode($img[1]);
		}else{
			unset($img);
			preg_match('/"(.*)" width="260"/',$html,$img);
			if(isset($img[1])){
				$data["img"] = rawurldecode($img[1]);
			}else{
				return false;
			}
		}
		//价格
		preg_match('/<strong>([\d]+)元/', $html, $price);
		if(isset($price[1])){
			$data["price"] = $price[1];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//时尚起义
	function getShiShang71($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/.* &gt;([^<]*)<\/td>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("","",$title[1]);
		}else{
			return false;
		}
		//图片
		preg_match('/"(.*)" width="260"/',$html,$img);
		if(isset($img[1])){
			$data["img"] = rawurldecode($img[1]);
		}else{
			unset($img);
			preg_match('/<td><img src=\"(.*)\" ><\/td>/',$html,$img);
			if(isset($img[1])){
				$data["img"] = rawurldecode($img[1]);
			}else{
				return false;
			}
		}
		//价格
		preg_match('/<strong>([\d]+)元/', $html, $price);
		if(isset($price[1])){
			$data["price"] = $price[1];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//拍拍
	function getPaiPai($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			$title=iconv('GBK','UTF-8',$title[1]);
			$data["title"] = str_replace("| 网购-拍拍网","",$title);
		}else{
			return false;
		}
		//图片
		$html=iconv('GBK','UTF-8',$html);
		$html = str_replace("picList:[[\"","<div id=\"demo\">",$html);
		$html = str_replace("\",\"","</div>",$html);
		preg_match('/<div id=\"demo\">([^<]*)<\/div>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<em id=\"commodityCurrentPrice\" promotType=\"\" sale=\"\" defaultVal=\"([^<]*)\">([^<]*)<\/em>/is', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//钻石小鸟
	function getZbird($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("","",$title[1]);
		}else{
			return false;
		}
		//图片
		preg_match('/<div id=\"sharepop\" name=\"sharepop\" style=\"display:none\" s_title=\"([^"]*)\" s_content=\"([^"]*)\" s_url=\"([^"]*)\" s_picurl=\"([^"]*)\"><\/div>/',$html,$img);
		if(isset($img[4])){
			$data["img"] = rawurldecode($img[4]);
		}else{
			return false;
		}
		//价格
		preg_match('/<span id=\"salePrice\" class=\"hide\">([^<]*)<span class=\"fontgray\">([^<]*)<\/span>([^<]*)<\/span>/is', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//第五大道
	function get5Lux($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("","",$title[1]);
		}else{
			return false;
		}
		//图片
		preg_match('/<a href=\"([^"]*)\" rel=\"lytebox\" title=\"([^"]*)\"><img src=\"([^"]*)\"[^>]*><\/a>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<font  class=\"shop\" id=\"ECS_SHOPPRICE\">([^<]*)<\/font>/is', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//兰缪
	function getLamiu($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("","",$title[1]);
		}else{
			return false;
		}
		//图片
		preg_match('/<img[^r]*id="main_img_url"[^r]*rc=\"([^"]*)\"[^>]*>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<span class=\"fz25 c61f\">([^<]*)<\/span>/is', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//梦芭莎
	function getMoonbasa($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<div class=\"p_info\">([^<>]*)<h2>([^<>]*)<span>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("","",$title[2]);
		}else{
			return false;
		}
		//图片
		preg_match('/<img[^r]*id="bigimg"[^r]*rc=\"([^"]*)\"[^>]*>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<b class=\"detailprice\">([^<]*)<\/b>/is', $html, $price);
		if(isset($price[0])){
			if (preg_match('/(\d+\.?\d+)(.*)/',$price[1],$matches))
				$data["price"] = $matches[1];
			else 
				$data["price"] = (int)$price[1];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//麦考林
	function getM18($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("M18_麦考林购物网","",$title[1]);
		}else{
			return false;
		}
		preg_match('/<img[^r]*styleId="([^"]*)"[^r]*rc=\"([^"]*)\"[^>]*>/',$html,$img);
		if(isset($img[2])){
			$data["img"] = $img[2];
		}else{
			return false;
		}
		//价格
		preg_match('/<span class=\"price\" id=\"stylePrice\">([^<]*)<\/span>/is', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	//麦包包
	function getMbaobao($html = '')
	{
		if ('' == $html)
			$html = $this->html;
			
		$data = array();
		//标题
		preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		if(isset($title[1])){
			$data["title"] = str_replace("- 麦包包网","",$title[1]);
		}else{
			return false;
		}
		//图片
		preg_match('/<img[^r]*rc=\"([^"]*)\"[^>]*class=\"js_goods_image_url\"[^>]*>/',$html,$img);
		if(isset($img[1])){
			$data["img"] = $img[1];
		}else{
			return false;
		}
		//价格
		preg_match('/<([a-z]+)[^i]*class=\"b_proprice\"[^>]*>([^<]*)<\/\\1>/is', $html, $price);
		if(isset($price[0])){
			$data["price"] = $price[0];
		}else{
			return false;
		}
		
		return $data;
	}
	
	public function downImg($imgUrl)
	{
		if($imgUrl == "") return false;
		
		$ext = substr(strrchr($imgUrl, "."), 1); 
        $ext = strtolower(substr(strrchr($imgUrl, "."), 1));
        
        if($ext!="gif" && $ext!="jpg" && $ext != "png" && $ext!="jpeg")
        {
        	//再判断一次 by zz @2011.06.27
        	$_fp = fopen($imgUrl, "rb");
        	$_bin = fread($_fp, 2); //只读2字节
        	fclose($_fp);
        	$_info  = @unpack("C2chars", $_bin);
        	$_code = intval($_info['chars1'].$_info['chars2']);
        	
        	$_fileType = "";
			switch($_code)
			{
        		case 7790:
					$_fileType = 'exe';
				break;
				case 7784:
					$_fileType = 'midi';
				break;
				case 8297:
					$_fileType = 'rar';
				break;
				case 255216:
					$_fileType = 'jpg';
				break;
				case 7173:
					$_fileType = 'gif';
				break;
				case 6677:
					$_fileType = 'bmp';
				break;
				case 13780:
					$_fileType = 'png';
				break;
				default:
					$_fileType = 'unknown';
			}
			if ($_info['chars1'] == '-1' && $_info['chars2'] == '-40'){ $_fileType = 'jpg';}
			if ($_info['chars1'] == '-119' && $_info['chars2'] == '80'){$_fileType = 'png';}

        	if ($_fileType != "gif" && $_fileType != "jpg" && $_fileType != "png") return false;
        	else $ext = $_fileType;
        	unset($_fp,$_bin,$_info,$_code,$_fileType);
        }
        
        $source_file = genSRpath($ext, 2);
        $source_file = str_replace("../" , "../../" , $source_file);
        @makeDir($source_file);
        $l_path = getPropath($source_file, 300, $ext);
        $m_path = getPropath($source_file, 200, $ext);
        $mm_path = getPropath($source_file, 210, $ext);
        $s_path = getPropath($source_file, 90, $ext);
        @makeDir($l_path);
        @makeDir($m_path);
        @makeDir($s_path);
        
        ob_start(); 
        @readfile($imgUrl); 
        $img = ob_get_contents(); 
        ob_end_clean(); 
        $size = strlen($img);
        if($size)
        {   
            $fp2 = @fopen($source_file, "a"); 
            fwrite($fp2, $img); 
            fclose($fp2);
            
            require_once("ImageMagick.php");
			$imageMagick = new ImageMagick();
			@$imageMagick->MagickThumb($source_file, $source_file , 600 , 600 , '' , 3);
			@$imageMagick->MagickThumb($source_file, $l_path , 300 , 300 , '' , 3);
			@$imageMagick->MagickThumb($source_file, $m_path , 200 , 200 , '' , 0 , 1);
			@$imageMagick->MagickThumb($source_file, $mm_path , 210 , 210 , '' , 0 , 1);
			@$imageMagick->MagickThumb($source_file, $s_path , 90 , 90 , '' , 3);

//			require_once("Image.php");
//			$imageMagick = new Image();
//			@$imageMagick->thumb($source_file, '' , $source_file , 400 , 400 , true , '');
//			@$imageMagick->thumb($source_file, '' , $l_path , 300 , 300 , true , '');
//			@$imageMagick->thumb($source_file, '' , $m_path , 200 , 200 , true , '');
//			@$imageMagick->thumb($source_file, '' , $s_path , 90 , 90 , true , '');

            $db_url = str_replace("../../", "./", $source_file);
            return $db_url; 
        }else
        	return false;
	}
}
?>