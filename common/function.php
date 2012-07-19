<?php
function getSignature()
{
	$s = array(
		$_SERVER['REMOTE_ADDR'],
		@$_SERVER['ALL_HTTP']
	);
		
	return md5(implode('|', $s));
}

function getExtensionName($filePath){
    $num=strrpos($filePath,'.');
    $len=strlen($filePath);
    $extension=substr($filePath,$num+1,$len-$num);
    return $extension;
}

function htmldecode($html , $isTitle = false)
{
	$html = nl2br($html);
    $html = str_replace(array("<br />","<br/>","<br>","\r","\n","\r\n","#在这里输入你想要说的话题#"), " ", $html);
//    $html = eregi_replace('<("|\')?([^ "\']*)("|\')?.*>([^<]*)<([^<]*)>', '\4', $html);
    $html = preg_replace('#<("|\')?([^ "\']*)("|\')?.*>([^<]*)<([^<]*)>#i', '\4', $html);
    $html = preg_replace('#</?.*?\>(.*?)</?.*?\>#i','',$html);
    $html = preg_replace('#(.*?)\[(.*?)\](.*?)javascript(.*?);(.*?)\[/(.*?)\](.*?)#','', $html);
    $html = preg_replace('#javascript(.*?)\;#','', $html);
    $html = htmlspecialchars($html);
    return($html);
}

function html($str)
{
    return htmlspecialchars($str,ENT_QUOTES);
}

function getbefore($time)
{
    $timestamp = time();
    $time 	= intval($time);
    $d 		= $timestamp-$time;
    if($time==0 || $d<=0)return false; 
    if(ceil($d/(360*24*60*60))>1){
        return array('d'=>floor($d/(360*24*60*60)),'f'=>0);
    }elseif(ceil($d/(30*24*60*60))>1){
        return array('d'=>floor($d/(30*24*60*60)),'f'=>1);
    }elseif(ceil($d/(24*60*60))>1){
        return array('d'=>floor($d/(24*60*60)),'f'=>2);
    }elseif(ceil($d/(60*60))>1){
        return array('d'=>floor($d/(60*60)),'f'=>3);
    }elseif(ceil($d/(60))>1){
        return array('d'=>floor($d/(60)),'f'=>4);
    }
    return array('d'=>1,'f'=>4);
}

//获取@用户列表
function getAtUnames($str)
{
	$namePattens =  "/ *@([\x{4e00}-\x{9fa5}A-Za-z0-9_]*) ?/u";
	preg_match_all($namePattens , $str , $matchs);
	
	$unameArr = array();
	foreach ($matchs[1] as $mRow){
		$unameArr[] = trim($mRow);
	}
	return $unameArr;
}

//获取话题列表
function getTopics($str)
{
	$topPattens = "/\#([^\#|.]+)\#/";
	preg_match_all($topPattens , $str , $matchs);
	
	$topArr = array();
	foreach ($matchs[1] as $mRow){
		$topArr[] = trim($mRow);
	}
	return $topArr;
}

//获取时间间隔
function transDate($timestamp) {
	$curTime = time();
	$space = $curTime - $timestamp;
	if($space < 60){
		$string = "刚刚";
		return $string;
	}elseif($space < 3600){
		$string = floor($space / 60) . "分钟前";
		return $string;
	}

	$curtimeArray = getdate($curTime);
	$timeArray = getDate($timestamp);
	if($curtimeArray['year'] == $timeArray['year']){
		if($curtimeArray['yday'] == $timeArray['yday']){
			$format = "%H:%M";
			$string = strftime($format, $timestamp);
			return "今天 {$string}";
		}elseif(($curtimeArray['yday'] - 1) == $timeArray['yday']){
			$format = "%H:%M";
			$string = strftime($format, $timestamp);
			return "昨天 {$string}";
		}else{
			$string = sprintf("%d月%d日 %02d:%02d", $timeArray['mon'], $timeArray['mday'], $timeArray['hours'],$timeArray['minutes']);
			return $string;
		}
	}

	$string = sprintf("%d年%d月%d日 %02d:%02d", $timeArray['year'], $timeArray['mon'], $timeArray['mday'],$timeArray['hours'], $timeArray['minutes']);  
	return $string;
}

//微博内容转换
function transWb($content)
{
	$topPattens 	= "/\#([^\#|.]+)\#/";
	$namePattens 	=  "/ *@([\x{4e00}-\x{9fa5}A-Za-z0-9_]*) ?/u";
	//替换微博中的话题
	$content = preg_replace($topPattens, '<a href="./?m=topic&a=ht&top=${1}">#${1}#</a>', $content);
	
	//替换微博中@的用户
	$content = preg_replace($namePattens, '<a href="./?m=user&a=nm&name=${1}">@${1}</a>', $content);

	//替换表情
	$faceArr = array('微笑','撇嘴','色','发呆','得意','流泪','害羞','睡','尴尬','呲牙','惊讶','冷汗','抓狂','偷笑','可爱','傲慢','困','流汗','大兵','咒骂','折磨','衰','擦汗','抠鼻','鼓掌','坏笑','左哼哼','右哼哼','鄙视','委屈','快哭了','阴险','亲亲','可怜','示爱','爱心','回头','挥手');
	foreach ($faceArr as $k=>$val){
		$content = str_replace("[$val]" , '<img src="./images/face/F_'.($k+1).'.gif">' , $content);
	}
	return $content;
}

function gotomail($mail) {
    $temp=explode('@',$mail);
    $t=strtolower($temp[1]);

    if ($t=='163.com') {
        return 'mail.163.com';
    } else if ($t=='vip.163.com') {
        return 'vip.163.com';
    } else if ($t=='126.com') {
        return 'mail.126.com';
    } else if ($t=='qq.com' || $t=='vip.qq.com' || $t=='foxmail.com') {
        return 'mail.qq.com';
    } else if ($t=='gmail.com') {
        return 'mail.google.com';
    } else if ($t=='sohu.com') {
        return 'mail.sohu.com';
    } else if ($t=='tom.com') {
        return 'mail.tom.com';
    } else if ($t=='vip.sina.com') {
        return 'vip.sina.com';
    } else if ($t=='sina.com.cn' || $t=='sina.com') {
        return 'mail.sina.com.cn';
    } else if ($t=='tom.com') {
        return 'mail.tom.com';
    } else if ($t=='yahoo.com.cn' || $t=='yahoo.cn') {
        return 'mail.cn.yahoo.com';
    } else if ($t=='tom.com') {
        return 'mail.tom.com';
    } else if ($t=='yeah.net') {
        return 'www.yeah.net';
    } else if ($t=='21cn.com') {
        return 'mail.21cn.com';
    } else if ($t=='hotmail.com') {
        return 'www.hotmail.com';
    } else if ($t=='sogou.com') {
        return 'mail.sogou.com';
    } else if ($t=='188.com') {
        return 'www.188.com';
    } else if ($t=='139.com') {
        return 'mail.10086.cn';
    } else if ($t=='189.cn') {
        return 'webmail15.189.cn/webmail';
    } else if ($t=='wo.com.cn') {
        return 'mail.wo.com.cn/smsmail';
    } else if ($t=='139.com') {
        return 'mail.10086.cn';
    } else {
        return '';
    }
}

function getsubstr($string, $start = 0,$sublen,$append=true) {
    $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
    preg_match_all($pa, $string, $t_string);

    if(count($t_string[0]) - $start > $sublen && $append==true) {
        return join('', array_slice($t_string[0], $start, $sublen))."...";
    } else {
        return join('', array_slice($t_string[0], $start, $sublen));
    }
}

function makeDeepDir($path)
{
	$dirs = explode("/" , $path);
	$root = $dirs[0]."/";
	for ($i =1; $i < count($dirs); $i ++)
	{
		$root .= $dirs[$i]."/";
		if (!is_dir($root))
			mkdir($root);
	}
}

function GetImageType($filename){
	$cw_error0 = ".jpg|.gif|.png|.bmp|.tiff|.jpeg|.JPEG|.JPG|.GIF|.PNG|.BMP";
	if(preg_match("/$cw_error0/i",$filename)){
		return $filename;
	}else{
		$filename .= '.jpg';
		return $filename;
	}
}

//针对QQ 设置type = 1
function GetImage($url, $filename = "" , $type = 0){
	if ($url == "") {
		return false;
	}
	if ($filename == ""){
		$ext = strrchr ( $url, "." );
		if ($ext != ".gif" && $ext != ".jpg"){
			return false;
		}
		$filename = time () . $ext;
	}
	//文件 保存路径
	if ($type){
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$img = curl_exec($ch);
		curl_close($ch);
	}else{
		ob_start ();
		readfile ( $url );
		$img = ob_get_contents ();
		ob_end_clean ();
	}
	$size = strlen ( $img );
	//文件大小
	$fp2 = @fopen ( $filename, "a" );
	fwrite ( $fp2, $img );
	fclose ( $fp2 );
	return $filename;
}
?>