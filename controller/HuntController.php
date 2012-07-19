<?php
Globals::requireClass('Controller');
Globals::requireTable('Brand');
Globals::requireTable('Product');
Globals::requireTable('Myitem');
Globals::requireTable('Mytag');
Globals::requireTable('Tag');
Globals::requireTable('User');
Globals::requireTable('Recommend');
Globals::requireTable('Prodcomm');
Globals::requireTable('Timeaxis');
Globals::requireTable('Likeitem');
Globals::requireTable('Usermsg');
Globals::requireTable('Friend');
Globals::requireTable('Pinxiu');
Globals::requireTable('Pxitems');
Globals::requireTable('Pic');

class HuntController extends Controller
{
	protected $brand;
	protected $product;
	protected $myitem;
	protected $tag;
	protected $mytag;
	protected $user;
	protected $recommend;
	protected $prodcomm;
	protected $timeaxis;
	protected $likeitem;
	protected $usermsg;
	protected $friend;
	protected $pinxiu;
	protected $pxitems;
	protected $pic;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->brand 		= new BrandTable($config);
		$this->product		= new ProductTable($config);
		$this->myitem		= new MyitemTable($config);
		$this->tag			= new TagTable($config);
		$this->mytag		= new MytagTable($config);
		$this->user			= new UserTable($config);
		$this->recommend	= new RecommendTable($config);
		$this->prodcomm		= new ProdcommTable($config);
		$this->timeaxis		= new TimeaxisTable($config);
		$this->likeitem		= new LikeitemTable($config);
		$this->usermsg		= new UsermsgTable($config);
		$this->friend		= new FriendTable($config);
		$this->pinxiu		= new PinxiuTable($config);
		$this->pxitems		= new PxitemsTable($config);
		$this->pic			= new PicTable($config);
	}
	
	/**
	 * 单品列表
	 * page			页数[默认0,1]
	 */
	public function huntlistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
//		$where[] = "source_site_url <> ''";
		$where[] = "del=0";
//		$where[] = "img_url <> ''";
//		$where[] = "title <> ''";
		
		$getFields =  $this->getParam("fields");

		global $BUY_URL,$TRYOUT_IMG_URL;
		$pageSize	= 18;
		$order = "id desc";
		$count		= $this->myitem->listCount($where);
		$fieldsMyitem = "id,uid,title,price,discount,img_url,ow,oh,summary,favor,likenum,commnum";
		$fieldsMyitem = $this->mergeFields($fieldsMyitem, $getFields);
		
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->myitem->listPageWithFields($fieldsMyitem, $where, $order ,$pageSize, $pageId);
		if(!$data){
			exit;
		}
		foreach ($data as $key => $value){
			$data[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
			$data[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
			$data[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
			$data[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
			$data[$key]['width'] = 200;
			$data[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 根据tag获取单品列表
	 * tagid		tagID
	 * page			页数[默认0,1]
	 */
	public function hunttaglistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$tagid = $this->getIntParam("tagid");
		$page = $this->getIntParam("page");
		if(!$page){
			$page = 1;
		}
		global $BUY_URL,$TRYOUT_IMG_URL;
		
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );
		$conf = array();
		$order = 'tide';
		$d  = 'up';
		$sort 	= in_array($order , array('tide','new','hot')) ? $order : 'hot';
		
		$sort_type	= array(
			'tide'	=>'rank desc , @id desc',
			'new'	=>'time_created DESC',
			'hot'	=>'rank desc , likenum DESC'
		);
		$conf	= array(
			'mode' 		=> SPH_MATCH_ALL,
			'index'		=> 'prod;d_prod',
//			'sortflag'	=> $d == "down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
			'sortflag'	=> SPH_SORT_EXTENDED,
			'sortmode'	=> $sort_type[$sort],
			'limit' 	=> 12,
			'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		);
		
		$cl->ResetFilters();
		$cl->ResetGroupBy();
		$cl->SetMatchMode( $conf['mode'] );
		$cl->SetLimits(($page-1)*$conf['limit'] , $conf['limit']);
		$cl->SetRankingMode( $conf['ranker'] );
		$cl->SetArrayResult( true );
		$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
		$cl->SetFilter('tag_id' , array($tagid));
		$cl->SetFilter('del' , array(0));
		$cl->SetFilter('flag' , array(1));
		$q = '';
		$res 	= $cl->Query($q, $conf['index']); 

		$list 	= $docids = array();
		$count	= 0;
		if($res !== false){
			if(is_array($res["matches"])){
				foreach($res["matches"] as $val)
				$docids[] = $val['id'];
			}

			if (count($docids)){
				$fieldsMyitem = "id,uid,title,price,discount,img_url,ow,oh,summary,favor,likenum,commnum";
				$fieldsMyitem = $this->mergeFields($fieldsMyitem, $this->getParam("fields"));
				$list	= $this->myitem->listAllWithFields($fieldsMyitem,"id in (".implode(',' , $docids).")" , 'id desc');
			}
		}
		
		$data = $list;
		foreach($data as $key=>$value){
			$data[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
			$data[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
			$data[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
			$data[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
			$data[$key]['width'] = 200;
			$data[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
		}
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 查询搭配里的可购买单品
	 * pxid			搭配ID
	 * ?m=hunt&a=pxitem&pxid=100
	 */
	public function pxitemAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$pxid = $this->getIntParam("pxid");
		
		$fieldsPxitems = "id,px_id,type,item_id,height,width,pos_top,pos_left,bg_status,v_flip,h_flip,z_index,img_url,b_id,sec_word,d_price";
		$pxitemlist = $this->pxitems->listAllWithFields($fieldsPxitems, array('px_id' => $pxid));
		if(!$pxitemlist){
			exit;
		}
		
		global $BUY_URL,$TRYOUT_IMG_URL;
		if($pxitemlist){
			$arr_itemid = array();
			foreach ($pxitemlist as $key_0 => $value_0){
				$arr_itemid[] = $value_0["item_id"];
			}
			$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,cat_1,cat_2,cat_3,bid,pid,type,tb_fav,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,tags,color,site_name,summary,del,flag,rank,view,favor,likenum,commnum,xjbnum,lxdnum,dpdnum,zfnum";
			$data = $this->myitem->getItemByIds($arr_itemid);
			foreach ($data as $key_1 => $value_1){
				$cat_1 = $value_1["cat_1"];
				$cat_2 = $value_1["cat_2"];
				$cat_3 = $value_1["cat_3"];
				if(($cat_1 > 3000) && ($cat_1 < 4000) || ($cat_1 == 3) || ($cat_1 == 31)){
					unset($data[$key_1]);
				}
				if(($cat_2 > 3000) && ($cat_2 < 4000) || ($cat_2 == 3) || ($cat_2 == 31)){
					unset($data[$key_1]);
				}
				if(($cat_3 > 3000) && ($cat_3 < 4000) || ($cat_3 == 3) || ($cat_3 == 31)){
					unset($data[$key_1]);
				}
			}
			foreach ($data as $key => $value){
				$data[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
				$data[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
				$data[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
				$data[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
				$data[$key]['width'] = 200;
				$data[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
			}
			$arr = array();
			foreach ($data as $key_2 => $value_2){
				$arr[] = $value_2;
			}
			echo $this->customJsonEncode($arr);
		}else{
			echo "";exit();
		}
	}
	
	/**
	 * 单品详情
	 * id	单品ID
	 */
	public function huntinfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam("id");
		global $BUY_URL,$TRYOUT_IMG_URL;
		
		$fieldsMyitem = "id,uid,cat_1,cat_2,cat_3,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
		$fieldsMyitem = $this->mergeFields($fieldsMyitem, $this->getParam("fieldsmyitem"));
		
		$huntinfo = $this->myitem->getRowWithFields($fieldsMyitem,$id);
		if(!$huntinfo){
			exit;
		}
			
		if($huntinfo){
			$huntinfo["source_site_url"] = $BUY_URL."?m=go&id=".$huntinfo['id'];
			$huntinfo['img_url_200'] = $TRYOUT_IMG_URL.getPropath($huntinfo['img_url'],200);
			$huntinfo['img_url_400'] = $TRYOUT_IMG_URL.$huntinfo['img_url'];
			$huntinfo['img_url'] = $TRYOUT_IMG_URL.$huntinfo['img_url'];
			$huntinfo['width'] = 200;
			$huntinfo['height'] = floor($huntinfo["oh"]*(200/$huntinfo["ow"]));
			
			$where 		= array('item_id' => $huntinfo['id'] , 'zf_id' => 0 , 'pl_id' => 0);
			$fieldsProdcomm =  $this->mergeFields("id,prod_id,item_id,uid,username,head_pic,comment", $this->getParam("fieldsprodcomm"));
			$prodcomm	= $this->prodcomm->listAllWithFields($fieldsProdcomm,$where, 'id desc');
			foreach ($prodcomm as $key => $value){
				$prodcomm[$key]["head_pic"] = $TRYOUT_IMG_URL.$value["head_pic"];
			}
			$huntinfo["prodcomm"] = $prodcomm;
			
			$fieldsUser = $this->mergeFields("id,username,head_pic,email,sex,city", $this->getParam("fieldsUser"));
			$userInfo = $this->user->getRowWithFields($fieldsUser,$huntinfo['uid']);
			
			$userInfo["head_pic"] = $TRYOUT_IMG_URL.$userInfo["head_pic"];
			$huntinfo["userinfo"] = $userInfo;
			
			echo $this->customJsonEncode($huntinfo);
		}else{
			echo "";exit();
		}
	}
	
	/**
	 * 单品分类
	 */
	public function itemcatAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		$itemcat = array();
		$itemcat[] = array('key' => '1582','value' => 'T恤');
		$itemcat[] = array('key' => '730','value' => '衬衫');
		$itemcat[] = array('key' => '647','value' => '牛仔裤');
		$itemcat[] = array('key' => '1774','value' => '短裤');
		$itemcat[] = array('key' => '1175','value' => '短裙');
		$itemcat[] = array('key' => '1884','value' => '长裙');
		$itemcat[] = array('key' => '1786','value' => '坡跟鞋');
		$itemcat[] = array('key' => '101','value' => '高跟鞋');
		$itemcat[] = array('key' => '30','value' => '连衣裙');
		$itemcat[] = array('key' => '2083','value' => '男士');	
		echo $this->customJsonEncode($itemcat);
	}
	
	/**
	 * 热门单品[tag热门]
	 */
	public function itemtagAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		$itemtag = array();
		$itemtag[] = array('key' => '4286','value' => '冰激凌');
		$itemtag[] = array('key' => '1830','value' => '糖果色');
		$itemtag[] = array('key' => '208','value' => '甜美');
		$itemtag[] = array('key' => '25','value' => '复古');
		$itemtag[] = array('key' => '30728','value' => '森系');
		$itemtag[] = array('key' => '63','value' => '雪纺');
		$itemtag[] = array('key' => '62','value' => '蕾丝');
		$itemtag[] = array('key' => '3351','value' => '透视');
		$itemtag[] = array('key' => '1244','value' => '手包');
		$itemtag[] = array('key' => '690','value' => '金属');
		$itemtag[] = array('key' => '2535','value' => '商务');
		$itemtag[] = array('key' => '221','value' => '派对');
		$itemtag[] = array('key' => '2','value' => '休闲');
		$itemtag[] = array('key' => '255','value' => '中性');
		$itemtag[] = array('key' => '423','value' => '欧美');
		echo $this->customJsonEncode($itemtag);
	}
	/**
	 * 单品搜索
	 */
	public function searcheitmeAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$query 	= $this->getParam('q');
		$query  = urldecode($query);
		
		$q	= '';
		if($query){
			$q 	= trim($query) ? '*'.trim($query).'*' : '';
		}
		
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient();
		$cl->SetServer ( $host, $port );
		
		$page 	= $this->getIntParam('page');
		$page 	= $page <= 0 ? 1 : $page;
		
		$d 		= in_array($this->getParam('d') , array('up','down')) ? $this->getParam('d') : 'up';
		$sort 	= "time";
		$sort 	= in_array($sort , array('view','time','favor','price')) ? $sort : 'view';
		$sort_type	= array(
	        'view'=>'view',
	        'favor'=>'favor',
	        'price'=>'price',
	        'time'=>'time_created'
        );
	  	$conf	= array(
	  		'mode' 		=> SPH_MATCH_ALL,
	  		'index'		=>'item;d_item',
	  		'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
	  		'sortmode'	=> $sort_type[$sort],
	  		'limit' 	=>12,
	  		'ranker' 	=> SPH_RANK_PROXIMITY_BM25
	  	);
		$cl->ResetFilters();
	  	$cl->ResetGroupBy();
	  	$cl->SetMatchMode( $conf['mode'] );
	  	$cl->SetLimits(($page-1)*$conf['limit'],$conf['limit']);
	  	$cl->SetRankingMode( $conf['ranker'] );
	  	$cl->SetArrayResult( true );
	  	$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
	  	$res 	= $cl->Query($q, $conf['index']); 
	  	//print_r($conf);die;
		if($res !== false){
	    	if(is_array($res["matches"])){
	      		foreach($res["matches"] as $val)
	        		$docids[] = $val['id'];
	    	}

	    	if (count($docids)){
	    		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,title,price,discount,img_url,ow,oh,summary,rank,view,favor,likenum,commnum";
		    	$fieldsMyitem = $this->mergeFields($fieldsMyitem, $this->getParam("fieldsmyitem"));
	    		$list	= $this->myitem->listAllWithFields($fieldsMyitem,"id in (".implode(',' , $docids).")" , 'id desc');
				$uid_arr = array();
		    	for($i=0;$i<count($list);$i++){
					
		        	$val = $list[$i];
					$uid_arr[] = $val['uid'];	
		       	 	$val['msrc'] = IMAGE_DOMAIN.getPropath($val['img_url'],200);
		        	$val['link'] = "/?m=mt&detail.php?id=".$val['id'];
		        	$val['wh'] = getWH(array($val['ow'],$val['oh']),95);
		        	$list[$i]=$val;
		        	unset($val);
		    	}
				
				//获取用户数据
				if (count($uid_arr)){
					$userList = $this->user->getUserByIds($uid_arr);
					unset($userList);
				}
	    	}
	    	
	    	$pageSize 	= $conf['limit'];
	    	$count		= $res['total_found'];
	    	$this->view->paging = $this->getPaging($count, $pageSize, $pageId);
	  	}
	  	global $BUY_URL,$TRYOUT_IMG_URL;
	  	if(count($list) > 0){
		  	foreach ($list as $key => $value){
		  		$list[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
				
				$list[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
				
				$list[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
				$list[$key]['width'] = 200;
				$list[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
				if(!isset($value["summary"])){
					$list[$key]["summary"] = '';
				}
				$list[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
		  	}
		  	echo $this->customJsonEncode($list);
	  	}else{
	  		echo "";
	  	}
	}
	
	/**
	 * 用户收藏单品列表
	 * uid			用户ID
	 * page			页数
	 */
	public function huntlinklistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$uid = $this->getIntParam("uid");
		$where 		= array('uid' => $uid);
		$order		= "id desc";
		$count 		= $this->likeitem->listCount($where);
		if($count==0)
		{
			exit;
		}
		$pageSize 	= 18;
		$this->view->paging 	= $this->getPaging($count , $pageSize , $pageId);
		$data 	= $this->likeitem->listPage($where , $order , $pageSize , $pageId);
	
		$arr_itemid = array();
		foreach ($data as $key_itemid => $value_itemid){
			$arr_itemid[] = $value_itemid["itemid"];
		}
		$itemid 	= '';
		$arr_itemid 	= array_unique($arr_itemid);
		$itemid 	= implode(',' , $arr_itemid);
		$itemid   	= trim($itemid , ',');
		
		$fieldsMyitem = "id,cat_1,cat_2,cat_3,uid,maincat_id,subcat_id,third_id,title,price,discount,img_url,ow,oh,summary,rank,view,favor,likenum,commnum";
		$fieldsMyitem = $this->mergeFields($fieldsMyitem, $this->getParam("fieldsmyitem"));
		
		$itemlist = $this->myitem->listAllWithFields($fieldsMyitem,"id in ($itemid)","id desc");
		
		unset($arr_itemid);
		unset($itemid);
		global $BUY_URL;
		foreach ($data as $key1 => $value1){
			$flag = 0;
			foreach ($itemlist as $key2 => $value2){
				if($value1["itemid"] == $value2["id"]){
					$value2["source_site_url"] = $BUY_URL."?m=go&id=".$value2['id'];
					$value2['img_url_200'] = IMAGE_DOMAIN.getPropath($value2['img_url'],200);
					$value2['img_url_400'] = IMAGE_DOMAIN.$value2['img_url'];
					$value2['img_url'] = IMAGE_DOMAIN.$value2['img_url'];
					$value2['width'] = 200;
					$value2['height'] = floor($value2["oh"]*(200/$value2["ow"]));
					
					$data[$key1]["iteminfo"] = $value2;
					$flag = 1;
				}
			}
			if($flag == 0){
				$data[$key1]["iteminfo"] = array();
			}
			unset($flag);
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 用户关注的物品列表
	 * uid			用户ID
	 * page			页数 
	 */
	public function favorarticleAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$uid = $this->getIntParam("uid");
		
		$followuidArr = $itemidArr = $pxidArr = $picidArr = array();
		
		$friendList = $this->friend->listAll(array('uid' => $uid) , 'id desc');
		if(!$friendList){
			echo "";exit;
		}
		foreach ($friendList as $fRow){
			$followuidArr[] = $fRow['friend_uid'];
		}
		unset($fRow);
		$followuidArr 	= array_unique($followuidArr);
		$foll_uid 	= implode(',' , $followuidArr);
		$foll_uid   	= trim($foll_uid , ',');
		
		$where[] = "uid in ($foll_uid)";
		$order		= "ctime desc";
		$count 		= $this->timeaxis->listCount($where);
		$pageSize 	= 18;
		$this->view->paging 	= $this->getPaging($count , $pageSize , $pageId);
		$articlelist 	= $this->timeaxis->listPage($where , $order , $pageSize , $pageId);
		//用户列表
		$userlist = $this->user->getUserByIds($followuidArr);
		unset($followuidArr);
		
		foreach ($articlelist as $article){
			switch ($article["type"]) {
				case 1:
					//单品
					$itemidArr[] = $article["postid"];
					break;
				case 2:
					//搭配
					$pxidArr[] = $article["postid"];
					break;
				case 3:
					//街拍
					$picidArr[] = $article["postid"];
					break;
				default:
					break;
			}
		}
		//单品列表
		$itemlist = $this->myitem->getItemByIds($itemidArr);
		unset($itemidArr);
		//搭配列表
		$pxlist = $this->pinxiu->getPxByIds($pxidArr);
		unset($pxidArr);
		//街拍列表
		$piclist = $this->pic->getPicByIds($picidArr);
		unset($picidArr);
		
		foreach ($articlelist as $key => $value){
			if($value["type"] == 1){
				foreach ($itemlist as $value_item){
					if($value["postid"] == $value_item["id"]){
						$value_item["img_url"] = IMAGE_DOMAIN.$value_item["img_url"];
						$articlelist[$key]["info"] = $value_item;
					}
				}
			}else if($value["type"] == 2){
				foreach ($pxlist as $value_px){
					if($value["postid"] == $value_px["id"]){
						$value_px["px_pic"] = IMAGE_DOMAIN.$value_px["px_pic"];
						$value_px["head_pic"] = IMAGE_DOMAIN.$value_px["head_pic"];
						$articlelist[$key]["info"] = $value_px;
					}
				}
			}else if($value["type"] == 3){
				foreach ($piclist as $value_pic){
					if($value["postid"] == $value_pic["id"]){
						$value_pic["img_url"] = IMAGE_DOMAIN.$value_pic["img_url"];
						$articlelist[$key]["info"] = $value_pic;
					}
				}
			}
			foreach ($userlist as $value_user){
				if($value["uid"] == $value_user["id"]){
					$value_user["head_pic"] = IMAGE_DOMAIN.$value_user["head_pic"];
					$articlelist[$key]["userinfo"] = $value_user;
				}
			}
		}
		unset($itemlist);
		unset($pxlist);
		unset($piclist);
		unset($userlist);
		
		echo $this->customJsonEncode($articlelist);
	}
	
	/**
	 * 网友物品列表
	 * page			页数
	 */
	public function articlelistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$where = array();
		$where["agroup"] = "1";
		$order = "ctime desc";
		
		$count = $this->timeaxis->articlecount($where);
		
		
		$pageSize	= 18;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->timeaxis->articlelist($where, $order ,$pageSize, $pageId);
		
		
		
		$itemidArr = $pxidArr = $picidArr = array();
		foreach ($data as $key1 => $article){
			switch ($article["type"]) {
				case 1:
					//单品
					$itemidArr[] = $article["postid"];
					break;
				case 2:
					//搭配
					$pxidArr[] = $article["postid"];
					break;
				case 3:
					//街拍
					$picidArr[] = $article["postid"];
					break;
				default:
					break;
			}
			$data[$key1]["head_pic"] = IMAGE_DOMAIN.$article["head_pic"];
		}
		//单品列表		
		$itemlist = $this->myitem->getItemByIds($itemidArr);
		unset($itemidArr);
		//搭配列表
		$pxlist = $this->pinxiu->getPxByIds($pxidArr);
		unset($pxidArr);
		//街拍列表
		$piclist = $this->pic->getPicByIds($picidArr);
		unset($picidArr);
		
		foreach ($data as $key => $value){
			if($value["type"] == 1){
				foreach ($itemlist as $value_item){
					if($value["postid"] == $value_item["id"]){
						$value_item["img_url"] = IMAGE_DOMAIN.$value_item["img_url"];
						$data[$key]["info"] = $value_item;
					}
				}
			}else if($value["type"] == 2){
				foreach ($pxlist as $value_px){
					if($value["postid"] == $value_px["id"]){
						$value_px["px_pic"] = IMAGE_DOMAIN.$value_px["px_pic"];
						$value_px["head_pic"] = IMAGE_DOMAIN.$value_px["head_pic"];
						$data[$key]["info"] = $value_px;
					}
				}
			}else if($value["type"] == 3){
				foreach ($piclist as $value_pic){
					if($value["postid"] == $value_pic["id"]){
						$value_pic["img_url"] = IMAGE_DOMAIN.$value_pic["img_url"];
						$data[$key]["info"] = $value_pic;
					}
				}
			}
		}
		unset($itemlist);
		unset($pxlist);
		unset($piclist);
		
		echo $this->customJsonEncode($data);
	}

	/**
	 * 品牌列表
	 */
	public function brandlistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$fieldsBrand = "id,name,enname,createname,origin,summary,pic,tmp_pic";
		$fieldsBrand=$this->mergeFields($fieldsBrand, $this->getParam("fields"));
		$where = "";
		$order = "id desc";
		$date = $this->brand->listAllWithFields($fieldsBrand,$where,$order);
		foreach($date as $id=>$vd)
		{
			$date[$id]['pic'] = IMAGE_DOMAIN.$date[$id]['pic'];
		}
		
		echo $this->customJsonEncode($date);
	}
	
	/**
	 * 品牌信息
	 * id			品牌ID
	 */
	public function brandinfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam("id");
		$fieldsBrand = "id,name,enname,createname,origin,summary,pic,tmp_pic";
		$fieldsBrand=$this->mergeFields($fieldsBrand, $this->getParam("fields"));
		$date = $this->brand->getRowWithFields($fieldsBrand,$id);
		if(!$data){
			exit;
		}
		echo $this->customJsonEncode($date);
	}
	
	/**
	 * 品牌的物品列表
	 * page			页数
	 */
	public function brandarticlelistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$where=array();
		$where["agroup"] = "3";
		$order = "ctime desc";
		
		$count = $this->timeaxis->articlecount($where);
		$pageSize	= 18;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->timeaxis->articlelist($where, $order ,$pageSize, $pageId);
		if(!$data){
			exit;
		}
		$itemidArr = $pxidArr = $picidArr = array();
		foreach ($data as $key1 => $article){
			switch ($article["type"]) {
				case 1:
					//单品
					$itemidArr[] = $article["postid"];
					break;
				case 2:
					//搭配
					$pxidArr[] = $article["postid"];
					break;
				case 3:
					//街拍
					$picidArr[] = $article["postid"];
					break;
				default:
					break;
			}
			$data[$key1]["head_pic"] = IMAGE_DOMAIN.$article["head_pic"];
		}
		//单品列表
		$itemlist = $this->myitem->getItemByIds($itemidArr);
		unset($itemidArr);
		//搭配列表
		$pxlist = $this->pinxiu->getPxByIds($pxidArr);
		unset($pxidArr);
		//街拍列表
		$piclist = $this->pic->getPicByIds($picidArr);
		unset($picidArr);
		
		foreach ($data as $key => $value){
			if($value["type"] == 1){
				foreach ($itemlist as $value_item){
					if($value["postid"] == $value_item["id"]){
						$value_item["img_url"] = IMAGE_DOMAIN.$value_item["img_url"];
						$data[$key]["info"] = $value_item;
					}
				}
			}else if($value["type"] == 2){
				foreach ($pxlist as $value_px){
					if($value["postid"] == $value_px["id"]){
						$value_px["px_pic"] = IMAGE_DOMAIN.$value_px["px_pic"];
						$value_px["head_pic"] = IMAGE_DOMAIN.$value_px["head_pic"];
						$data[$key]["info"] = $value_px;
					}
				}
			}else if($value["type"] == 3){
				foreach ($piclist as $value_pic){
					if($value["postid"] == $value_pic["id"]){
						$value_pic["img_url"] = IMAGE_DOMAIN.$value_pic["img_url"];
						$data[$key]["info"] = $value_pic;
					}
				}
			}
		}
		unset($itemlist);
		unset($pxlist);
		unset($piclist);
		
		echo $this->customJsonEncode($data);
		
	}
	
	/**
	 * 名人列表
	 * page			页数
	 */
	public function userpersonlistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		$where=array();
		$where[] = "agroup=2";
		$count		= $this->user->listCount($where);
		$pageSize	= 18;
		$order = "id desc";
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$fieldsUser = "id,username,head_pic,email,sex,city";
		$fieldsUser = $this->mergeFields($fieldsUser, $this->getParam("fieldsuser"));
		$data	= $this->user->listPageWithFields($fieldsUser,$where, $order ,$pageSize, $pageId);
		foreach ($data as $key => $value){
			$data[$key]["head_pic"] = IMAGE_DOMAIN.$value["head_pic"];
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 名人详情
	 * id		名人ID
	 * ?m=hunt&a=userpersoninfo&id=2
	 */
	public function userpersoninfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$fieldsUser = "id,username,head_pic,email,sex,city";
		$fieldsUser = $this->mergeFields($fieldsUser, $this->getParam("fieldsuser"));
		
		$id = $this->getIntParam("id");
		$data = $this->user->getRowWithFields($fieldsUser,$id);
		if(!$data){
			exit;
		}
		$data["head_pic"] = IMAGE_DOMAIN.$data["head_pic"];
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 名人物品列表
	 * page			页数
	 */
	public function personarticlelistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		$where=array();
		$where["agroup"] = "2";
		$order = "ctime desc";
		
		$count = $this->timeaxis->articlecount($where);
		
		$pageSize	= 18;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->timeaxis->articlelist($where, $order ,$pageSize, $pageId);
		$itemidArr = $pxidArr = $picidArr = array();
		foreach ($data as $key1 => $article){
			switch ($article["type"]) {
				case 1:
					//单品
					$itemidArr[] = $article["postid"];
					break;
				case 2:
					//搭配
					$pxidArr[] = $article["postid"];
					break;
				case 3:
					//街拍
					$picidArr[] = $article["postid"];
					break;
				default:
					break;
			}
			$data[$key1]["head_pic"] = IMAGE_DOMAIN.$article["head_pic"];
		}
		//单品列表
		$itemlist = $this->myitem->getItemByIds($itemidArr);
		unset($itemidArr);
		//搭配列表
		$pxlist = $this->pinxiu->getPxByIds($pxidArr);
		unset($pxidArr);
		//街拍列表
		$piclist = $this->pic->getPicByIds($picidArr);
		unset($picidArr);
		
		foreach ($data as $key => $value){
			if($value["type"] == 1){
				foreach ($itemlist as $value_item){
					if($value["postid"] == $value_item["id"]){
						$value_item["img_url"] = IMAGE_DOMAIN.$value_item["img_url"];
						$data[$key]["info"] = $value_item;
					}
				}
			}else if($value["type"] == 2){
				foreach ($pxlist as $value_px){
					if($value["postid"] == $value_px["id"]){
						$value_px["px_pic"] = IMAGE_DOMAIN.$value_px["px_pic"];
						$value_px["head_pic"] = IMAGE_DOMAIN.$value_px["head_pic"];
						$data[$key]["info"] = $value_px;
					}
				}
			}else if($value["type"] == 3){
				foreach ($piclist as $value_pic){
					if($value["postid"] == $value_pic["id"]){
						$value_pic["img_url"] = IMAGE_DOMAIN.$value_pic["img_url"];
						$data[$key]["info"] = $value_pic;
					}
				}
			}
		}
		unset($itemlist);
		unset($pxlist);
		unset($piclist);
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 最新物品列表
	 */
	public function newarticlelistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$where["agroup"] = "";
		$order = "ctime desc";
		
		$count = $this->timeaxis->articlecount($where);
		$pageSize	= 18;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->timeaxis->articlelist($where, $order ,$pageSize, $pageId);
		
		$itemidArr = $pxidArr = $picidArr = array();
		foreach ($data as $key1 => $article){
			switch ($article["type"]) {
				case 1:
					//单品
					$itemidArr[] = $article["postid"];
					break;
				case 2:
					//搭配
					$pxidArr[] = $article["postid"];
					break;
				case 3:
					//街拍
					$picidArr[] = $article["postid"];
					break;
				default:
					break;
			}
			$data[$key1]["head_pic"] = IMAGE_DOMAIN.$article["head_pic"];
		}
		//单品列表
		$itemlist = $this->myitem->getItemByIds($itemidArr);
		unset($itemidArr);
		//搭配列表
		$pxlist = $this->pinxiu->getPxByIds($pxidArr);
		unset($pxidArr);
		//街拍列表
		$piclist = $this->pic->getPicByIds($picidArr);
		unset($picidArr);
		
		foreach ($data as $key => $value){
			if($value["type"] == 1){
				foreach ($itemlist as $value_item){
					if($value["postid"] == $value_item["id"]){
						$value_item["img_url"] = IMAGE_DOMAIN.$value_item["img_url"];
						$data[$key]["info"] = $value_item;
					}
				}
			}else if($value["type"] == 2){
				foreach ($pxlist as $value_px){
					if($value["postid"] == $value_px["id"]){
						$value_px["px_pic"] = IMAGE_DOMAIN.$value_px["px_pic"];
						$value_px["head_pic"] = IMAGE_DOMAIN.$value_px["head_pic"];
						$data[$key]["info"] = $value_px;
					}
				}
			}else if($value["type"] == 3){
				foreach ($piclist as $value_pic){
					if($value["postid"] == $value_pic["id"]){
						$value_pic["img_url"] = IMAGE_DOMAIN.$value_pic["img_url"];
						$data[$key]["info"] = $value_pic;
					}
				}
			}
		}
		unset($itemlist);
		unset($pxlist);
		unset($piclist);
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 摇一摇,随机物品列表
	 */
	public function randarticlelistAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$date = date("Y-m-d",strtotime("-60 day"));
		$time = strtotime($date." 00:00:00");
		$where[] = "ctime >= $time";

		$pageSize	= 18;
		$data = $this->timeaxis->listRand($where,$pageSize);
		
		$itemidArr = $pxidArr = $picidArr = array();
		foreach ($data as $key1 => $article){
			switch ($article["type"]) {
				case 1:
					//单品
					$itemidArr[] = $article["postid"];
					break;
				case 2:
					//搭配
					$pxidArr[] = $article["postid"];
					break;
				case 3:
					//街拍
					$picidArr[] = $article["postid"];
					break;
				default:
					break;
			}
			$data[$key1]["head_pic"] = IMAGE_DOMAIN.$article["head_pic"];
		}
		//单品列表
		$itemlist = $this->myitem->getItemByIds($itemidArr);
		unset($itemidArr);
		//搭配列表
		$pxlist = $this->pinxiu->getPxByIds($pxidArr);
		unset($pxidArr);
		//街拍列表
		$piclist = $this->pic->getPicByIds($picidArr);
		unset($picidArr);
		
		foreach ($data as $key => $value){
			if($value["type"] == 1){
				foreach ($itemlist as $value_item){
					if($value["postid"] == $value_item["id"]){
						$value_item["img_url"] = IMAGE_DOMAIN.$value_item["img_url"];
						$data[$key]["info"] = $value_item;
					}
				}
			}else if($value["type"] == 2){
				foreach ($pxlist as $value_px){
					if($value["postid"] == $value_px["id"]){
						$value_px["px_pic"] = IMAGE_DOMAIN.$value_px["px_pic"];
						$value_px["head_pic"] = IMAGE_DOMAIN.$value_px["head_pic"];
						$data[$key]["info"] = $value_px;
					}
				}
			}else if($value["type"] == 3){
				foreach ($piclist as $value_pic){
					if($value["postid"] == $value_pic["id"]){
						$value_pic["img_url"] = IMAGE_DOMAIN.$value_pic["img_url"];
						$data[$key]["info"] = $value_pic;
					}
				}
			}
		}
		unset($itemlist);
		unset($pxlist);
		unset($piclist);
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 穿衣管家推荐的单品列表[每天推荐的单品]
	 * day			天数[默认0[今天],1[昨天],2[前天]]
	 * page			页数
	 */
	public function huntlistrAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$where[] = "delstatus = 1";
		$where[] = "recommendimgurl <> ''";
		$where[] = "title <> ''";
		
		$day = $this->getIntParam("day");
		if($day == 0){
			$date = date("Y-m-d",time());
			$time_min = strtotime($date." 00:00:00");
			$time_max = strtotime($date." 23:59:59");
		}else{
			$date = date("Y-m-d",strtotime("-$day day"));
			$time_min = strtotime($date." 00:00:00");
			$time_max = strtotime($date." 23:59:59");
		}
		$where[] = "(recommenddate >= $time_min and recommenddate <= $time_max)";
		$count		= $this->recommend->listCount($where);
		if($day > 0 && $count == 0){echo "";exit;}
		if($count == 0){
			unset($where);
			$where[] = "delstatus = 1";
			$where[] = "recommendimgurl <> ''";
			$where[] = "title <> ''";
			$date = date("Y-m-d",strtotime("-1 day"));
			$time_min = strtotime($date." 00:00:00");
			$time_max = strtotime($date." 23:59:59");
			$where[] = "(recommenddate >= $time_min and recommenddate <= $time_max)";
			$count		= $this->recommend->listCount($where);
		}
		
		global $BUY_URL;
		$pageSize	= 18;
		$order = "id desc";
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$fieldsRecommend = "id,itemid,title,price,details,recommendimgurl,recommendimgurlfile,ow,oh";
		$fidldsRecommend = $this->mergeFields($fieldsRecommend, $this->getParam("fieldsrommend"));
		
		$data	= $this->recommend->listPageWithFields($fieldsRecommend,$where, $order ,$pageSize, $pageId);
		foreach ($data as $key => $value){
			//420,196,140
			$data[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['itemid'];
			$data[$key]['img_url_196'] = IMAGE_DOMAIN.getPropath($value['recommendimgurl'],196);
			$data[$key]['width'] = 196;
			$data[$key]['height'] = floor($value["oh"]*(196/$value["ow"]));
		}
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 穿衣管家中的推荐单品的详情
	 * itemid		单品ID
	 */
	public function huntinforAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam("itemid");
		global $BUY_URL;
		$fieldsRecommend = "id,itemid,title,price,details,recommendimgurl,recommendimgurlfile,ow,oh";
		$huntinfo = $this->recommend->getRowWithFields($fieldsRecommend,array("itemid"=>$id));
		if($huntinfo){
			$huntinfo["source_site_url"] = $BUY_URL."?m=go&id=".$huntinfo['itemid'];
			$huntinfo['img_url_196'] = IMAGE_DOMAIN.getPropath($huntinfo['recommendimgurl'],196);
			$huntinfo['width'] = 196;
			$huntinfo['height'] = floor($huntinfo["oh"]*(196/$huntinfo["ow"]));
			
			$where 		= array('item_id' => $huntinfo['itemid'] , 'zf_id' => 0 , 'pl_id' => 0);
			$fieldsProdcomm = "id,prod_id,item_id,uid,username,head_pic,comment";
			$prodcomm	= $this->prodcomm->listAllWithFields($fieldsProdcomm,$where, 'id desc');
			foreach ($prodcomm as $key => $value){
				$prodcomm[$key]["head_pic"] = IMAGE_DOMAIN.$value["head_pic"];
			}
			$huntinfo["prodcomm"] = $prodcomm;
			
			$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
			
			$myitem = $this->myitem->getRowWithFields($fieldsMyitem,$huntinfo['itemid']);
			$fieldsUser = "id,username,head_pic,email,sex,city";
			$userInfo = $this->user->getRowWithFields($fieldsUser,$myitem['uid']);
			$userInfo["head_pic"] = IMAGE_DOMAIN.$userInfo["head_pic"];
			$huntinfo["userinfo"] = $userInfo;
			
			echo $this->customJsonEncode($huntinfo);
		}else{
			echo "";exit();
		}
	}
	
	
	
	//
	/**
	 * 根据tagID获取相关的单品列表
	 * tagid			tagID
	 *
	 */
	public function tagmyitemAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$params = $this->getParams('tagid,page');
		$pageId = $params["page"];
		if(empty($pageId) && !$pageId){
			$pageId = 1;
		}
		$showPageSize = $this->pageSize*$this->cascade;
	
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );
		$conf = array();
	
		$d  = 'up';
		$sort 	= 'tide';
	
		$sort_type	= array(
				'tide'	=>'rank desc , @id desc',
				'new'	=>'time_created DESC',
				'hot'	=>'rank desc , likenum DESC'
		);
		$conf	= array(
				'mode' 		=> SPH_MATCH_ALL,
				'index'		=> 'prod;d_prod',
				//			'sortflag'	=> $d == "down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
				'sortflag'	=> SPH_SORT_EXTENDED,
				'sortmode'	=> $sort_type[$sort],
				'limit' 	=> 12,
				'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		);
	
		$cl->ResetFilters();
		$cl->ResetGroupBy();
		$cl->SetMatchMode( $conf['mode'] );
		$cl->SetLimits(($pageId)*$conf['limit'] , $conf['limit']);
		$cl->SetRankingMode( $conf['ranker'] );
		$cl->SetArrayResult( true );
		$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
	
		$cl->SetFilter('tag_id' , array($params["tagid"]));
		$cl->SetFilter('del' , array(0));
		//		$cl->SetFilter('flag' , array(1));
	
		$q = '';
		$res 	= $cl->Query($q, $conf['index']);
		$list = array();
		if($res !== false){
			if(is_array($res["matches"])){
				foreach($res["matches"] as $val)
					$docids[] = $val['id'];
			}
	
			if (count($docids)){
				$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
				
				$list	= $this->myitem->listAllWithFields($fieldsMyitem,"id in (".implode(',' , $docids).")" , 'id desc');
				$uid_arr = array();
				for($i=0;$i<count($list);$i++){
						
					$val = $list[$i];
					$uid_arr[] = $val['uid'];
					$val['msrc'] = IMAGE_DOMAIN.getPropath($val['img_url'],200);
					$val['link'] = "/?m=mt&detail.php?id=".$val['id'];
					$val['wh'] = getWH(array($val['ow'],$val['oh']),95);
					$list[$i]=$val;
					unset($val);
				}
	
				//获取用户数据
				if (count($uid_arr)){
					$userList = $this->user->getUserByIds($uid_arr);
					unset($userList);
				}
			}
			$count		= $res['total_found'];
			$this->view->paging = $this->getPaging($count, $pageSize, $pageId , 3);
		}
		global $BUY_URL,$TRYOUT_IMG_URL;
		if(count($list) > 0){
			foreach ($list as $key => $value){
				$list[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
	
				$list[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
	
				$list[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
				$list[$key]['width'] = 200;
				$list[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
				if(!isset($value["summary"])){
					$list[$key]["summary"] = '';
				}
				$list[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
			}
			echo $this->customJsonEncode($list);
		}else{
			echo "";
		}
	}
	
	
	/**
	 * 根据风格分类获取单品列表
	 * typeid			风格分类ID[不可以为空]	气场女王[4],职场摩登[1],清新森系[7],休闲星期五[10],约会达人[11],夜场秀[2],闺蜜逛街[8],派对女王[5],男士正装[3],休闲格调[6],品位IT男[9],英伦风范[12]
	 * pageid			页码
	 * ?m=hunt&a=stylemyitems&typeid=4
	 */
	public function stylemyitemsAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		global $BUY_URL,$TRYOUT_IMG_URL;
		
		$pxid = $this->getIntParam("typeid");
	
		$where = array();
		$where[] = "style = $pxid";
		Globals::requireTable('HomeProd');
		$homeprod = new HomeProdTable($this->config);
		$count		= $homeprod->listCount($where);
		$pageSize	= 12;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $homeprod->listPage($where, 'id desc', $pageSize, $pageId);
		if(!$data){
			exit;
		}
	
		foreach ($data as $key_0 => $value_0){
			$arr_itemid[] = $value_0["connid"];
		}
		Globals::requireTable('Myitem');
		$homemyitem = new MyitemTable($this->config);
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
		
		$itemsArr = $homemyitem->listAllWithFields($fieldsMyitem,"id in (".implode(',' , $arr_itemid).")" , 'id desc');
		foreach ($itemsArr as $key => $value){
			$itemsArr[$key]["source_site_url"] = $BUY_URL."?m=go&id=".$value['id'];
			$itemsArr[$key]['img_url_200'] = $TRYOUT_IMG_URL.getPropath($value['img_url'],200);
			$itemsArr[$key]['img_url_400'] = $TRYOUT_IMG_URL.$value['img_url'];
			$itemsArr[$key]['img_url'] = $TRYOUT_IMG_URL.$value['img_url'];
			$itemsArr[$key]['width'] = 200;
			$itemsArr[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
		}
		
		echo $this->customJsonEncode($itemsArr);
		exit();
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

Config::extend('HuntController', 'Controller');
