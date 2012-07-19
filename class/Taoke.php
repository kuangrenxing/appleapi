<?php
/**
 +------------------------------------------------------------------------------
 * 淘宝客 sample类
 +------------------------------------------------------------------------------
 */
class Taoke
{
	public $appKey 		= "";
	public $appSecret 	= "";
	public $tbkID 		= "";
	public $appUrl 		= "http://gw.api.taobao.com/router/rest?";
	
	public function getTBkUrl($tbid = 0)
	{
		if ($tbid){
			$paramArr = array(
			     'app_key' 		=> $this->appKey,
			     'method' 		=> 'taobao.taobaoke.items.convert',
			     'format' 		=> 'json',
			     'v' 			=> '2.0',
			     'sign_method'	=>'md5',
			     'timestamp' 	=> date('Y-m-d H:i:s'),
			     'fields' 		=> 'click_url,num_iid,shop_click_url',
			  'num_iids' 		=> $tbid,
			     'pid' 			=> $this->tbkID
			);
			//生成签名
			$sign 		= self::createSign($paramArr);
			//组织参数
			$strParam 	= self::createStrParam($paramArr);
			$strParam 	.= 'sign='.$sign;
			//访问服务
			$url 		= $this->appUrl.$strParam;
			$result 	= file_get_contents($url);
			$result 	= json_decode($result);
			$response 	= $result->taobaoke_items_convert_response;
			
			if (property_exists($response , 'taobaoke_items')){
				$items 		= $response->taobaoke_items;
				return $items->taobaoke_item[0]->click_url;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//签名函数
	function createSign ($paramArr) {
	     $sign = $this->appSecret;
	     ksort($paramArr);
	     foreach ($paramArr as $key => $val) {
	         if ($key !='' && $val !='') {
	             $sign .= $key.$val;
	         }
	     }
	     $sign	.= $this->appSecret;
	     $sign 	= strtoupper(md5($sign));
	     return $sign;
	}
	
	//组参函数
	function createStrParam ($paramArr) {
	     $strParam = '';
	     foreach ($paramArr as $key => $val) {
	     if ($key !='' && $val !='') {
	             $strParam .= $key.'='.urlencode($val).'&';
	         }
	     }
	     return $strParam;
	}
}
?>