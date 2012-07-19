<?php
/**生成原图路径
  *
  */
if (!isset($timestamp)) $timestamp = time();

function genSRpath($ext,$type)
{
    global $timestamp;
	$ym  = date('Ym' , $timestamp);
	$d   = date('d' , $timestamp);
	$h   = date('g' , $timestamp);
    $fname = genFileName($ext);
    switch($type){
        case 1://brand
            return  BRAND_IMG_PATH.$ym.'/'.$d.'/'.$h."/".$fname;
            break;
        case 2://u_img
            return USER_IMG_PATH.$ym.'/'.$d.'/'.$h.'/'.$fname;
            break;
        case 3://prod
            return PROD_IMG_PATH.$ym.'/'.$d.'/'.$h.'/'.$fname;
            break;
        case 4://px
            return PINXIU_IMG_PATH.$ym.'/'.$d.'/'.$h.'/'.$fname;
            break;
        default:break;
    }
}

//根据sr地址   生成 pro地址 
//根据pro地址  生成 pro地址
//sr =>pro
//$l = getPropath('./img/pro/sys_img/200909/22/1.jpeg',array('l'=>400,'rmbg'=>1,'vflip'=>1),'',0);
//pro =>pro
//$prol = getPropath($l,array('l'=>400,'rmbg'=>1,'vflip'=>1),'',1);
//不指定l m s 即对原图操作存放的位置,否则生成对指定cache图片操作生成的位置
//$ol = getPropath('./img/pro/sys_img/200909/22/1.jpeg',array('rmbg'=>1,'vflip'=>1),'',0);
function getPropath($path,$param,$predepth=0)
{
	$size = is_array($param) ? $param[0] : $param;
	
	if (strpos($path , "_400") !== false)
		$prodDir = str_replace('_400' , "_".$size , $path);
	else if (strpos($path , "_300") !== false)
		$prodDir = str_replace('_300' , "_".$size , $path);
	else
		$prodDir = substr($path , 0 , strrpos($path , '.'))."_".$size.".".substr(strrchr($path, "."), 1);
	
//	if (120 == $size && strpos($prodDir , "proimg") === false){
//		if (file_exists($prodDir)){}
//		else 
//			$prodDir = str_replace('_120' , "_160" , $prodDir);
//	}
    return $prodDir;
}

//生成唯一文件名
function genFilename($ext='png')
{
    global $timestamp;
	srand ((double) microtime () *1000000 ) ;
	return substr(md5 (uniqid(rand())),0,5).$timestamp.'.'.strtolower($ext);
}
//生成拼秀原始图片路径
function genPXpath($ext='jpg')
{
    global $timestamp;
	$ym  = date('Ym' , $timestamp);
	$d 	 = date('d' , $timestamp);
	$h   = date('g' , $timestamp);
    $fname = genFileName($ext);
	return PINXIU_IMG_PATH.$ym.'/'.$d.'/'.$h.'/'.$fname;
}
//获取拼秀其他尺寸路径
function getPXpath($sourcepath,$size=320)
{
    if (strpos($sourcepath , "_500") !== false)
		$cachepath = str_replace('_500' , "_".$size , $sourcepath);
	else 
		$cachepath = substr($sourcepath , 0 , strrpos($sourcepath , '.'))."_".$size.".".substr(strrchr($sourcepath, "."), 1);
	
    return $cachepath;
}

//获取街拍其他尺寸路径
function getLookBookpath($sourcepath,$size=320)
{
    if (strpos($sourcepath , "_560") !== false)
		$cachepath = str_replace('_560' , "_".$size , $sourcepath);
	else 
		$cachepath = substr($sourcepath , 0 , strrpos($sourcepath , '.'))."_".$size.".".substr(strrchr($sourcepath, "."), 1);
	
    return $cachepath;
}

//获取照片其他尺寸路径
function getPicPath($sourcepath,$size=200)
{
    if (strpos($sourcepath , "_300") !== false)
		$cachepath = str_replace('_300' , "_".$size , $sourcepath);
	else 
		$cachepath = substr($sourcepath , 0 , strrpos($sourcepath , '.'))."_".$size.".".substr(strrchr($sourcepath, "."), 1);
	
    return $cachepath;
}

//获取用户其他尺寸路径
function getUserPath($sourcepath,$size=180)
{
    if (strpos($sourcepath , '20091229/57/20091229/2009122910464372.jpg' !== false))
    	return $sourcepath;
    	
    if (strpos($sourcepath , '20091229/57/20091229/2009122910523310.jpg' !== false))
    	return $sourcepath;
    	
    if (strpos($sourcepath , '/male.jpg' !== false))
    	return $sourcepath;
    	
    if (strpos($sourcepath , '/female.jpg' !== false))
    	return $sourcepath;
    	
	if (strpos($sourcepath , "_180") !== false)
		$cachepath = str_replace('_180' , "_".$size , $sourcepath);
	else 
		$cachepath = substr($sourcepath , 0 , strrpos($sourcepath , '.'))."_".$size.".".substr(strrchr($sourcepath, "."), 1);
	
    return $cachepath;
}

//根据路径生成目录
function makeDir($path,$chmod=0777)
{
	$dirstack = array();
	$dir =false;
	while($path =dirname($path))
	{
		if(is_dir($path))break;
		array_unshift($dirstack,$path);
	}
	foreach($dirstack as $key=>$val)
	{
		if(is_dir($val))continue;
		mkdir($val,$chmod);
		$dir = $val;
	}
	return $dir;
}

//等比缩放
function getWH($ar,$limit=80)
{
    $wh	= array();
    $wh[0]	= intval($ar[0]);
    $wh[1]	= intval($ar[1]);
    $limit 	= intval($limit);
    if($wh[0]<=0 || $wh[1]<=0 || $limit<=0)return array($limit,$limit);
    $rate 	= $wh[0]/$wh[1];
    if($rate>1){
        $wh[0]	= $limit;
        $wh[1]	= ceil($limit/$rate);
    }else{
        $wh[1]	= $limit;
        $wh[0]	= ceil($limit*$rate);
    }
    return $wh;
}

//从数据库pxitems-js SBItems
function tojsItem($item)
{
    global $ItemFilter;
    $keys 	= array_flip($ItemFilter);
    $ret 	= array();
    if(!is_array($item)) return $ret;
    foreach($item as $key => $val){
        if(in_array($key,$keys)){
            $ret[$ItemFilter[$key]] = $val;
        }
    }
    $ret["lsrc"]	= PINXIU_DOMAIN.$ret['lsrc'];
    if(count($ret) >0 && $item['id']>0)
        $ret['itemid'] = $item['id'];
    return $ret;
}

//获取单品左边分类
function getProductCateOptions($catid = -1)
{
    global $product_cat_1;

    $Options = $dot = '';
    foreach($product_cat_1 as $key=>$val)
    {
        $dot = '';
        $Options .= "<option value='$key' ".($key==$catid?"selected":"").">$dot $val";
        $sub2 	= $GLOBALS['product_cat_2_'.$key];
        $sub2 	= is_array($sub2) ? $sub2 : array();
        foreach($sub2 as $k=>$v)
        {
            $dot	= '&nbsp;&nbsp;';         
            $Options .= "<option value='$k' ".($k == $catid ? "selected" : "").">$dot $v";
            $sub3 	= $GLOBALS['product_cat_3_'.$k];
            $sub3 	= is_array($sub3)?$sub3:array();
            foreach($sub3 as $k1=>$v1)
            {
                $dot	='&nbsp;&nbsp;&nbsp;&nbsp;';
                $Options .= "<option value='$k1' ".($k1 == $catid ? "selected" : "").">$dot $v1";
            }
        }
    }
    return $Options;
}