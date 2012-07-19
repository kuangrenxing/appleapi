<?php
Globals::requireClass('Controller');
Globals::requireTable('User');
Globals::requireTable('Myitem');
Globals::requireTable('Recommend');
Globals::requireTable('AppDressmain');
Globals::requireTable('AppDressprod');
Globals::requireTable('AppDresslog');
Globals::requireTable('AppDressrecomm');
Globals::requireTable('Connect');
Globals::requireTable('Prizeset');
Globals::requireTable('AppDressManage');
Globals::requireTable('Favorites');
Globals::requireTable('AppDressEvaltake');

class DressController extends Controller
{
	protected $myitem;
	protected $recommend;
	protected $user;
	protected $dress;
	protected $prod;
	protected $log;
	protected $recomm;
	protected $connect;
	protected $prizeset;
	protected $appdressmange;
	protected $favorites;
	protected $evaltake;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->myitem			= new MyitemTable($config);
		$this->recommend		= new RecommendTable($config);
		$this->user				= new UserTable($config);
		$this->dress			= new AppDressmainTable($config);
		$this->prod				= new AppDressprodTable($config);
		$this->log				= new AppDresslogTable($config);
		$this->recomm			= new AppDressrecommTable($config);
		$this->connect			= new ConnectTable($config);
		$this->prizeset			= new PrizesetTable($config);
		$this->appdressmange	= new AppDressManageTable($config);
		$this->favorites		= new FavoritesTable($config);
		$this->evaltake			= new AppDressEvaltakeTable($config);
	}
	
	/**
	 * 微博用户登录判断
	 * id			int64	用户UID
	 * screen_name	string	用户昵称
	 * province		int		用户所在地区ID
	 * city			int		用户所在城市ID
	 * gender		string	性别，m：男、f：女、n：未知
	 * avatar_large	string	用户大头像地址
	 * token				绑定后获取的值
	 * connfrom				用户信息的来源[1---iphone,2---android]默认：1---iphone
	 */
	public function wbuserAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('id,screen_name,province,city,gender,avatar_large,token,token_secret,connfrom');
		if(empty($params['id']) || trim($params['id']) == ''){
			echo "";
			return;
		}
		if(empty($params['screen_name']) || trim($params['screen_name']) == ''){
			echo "";
			return;
		}
		if(empty($params['province']) || trim($params['province']) == ''){
			echo "";
			return;
		}
		if(empty($params['city']) || trim($params['city']) == ''){
			echo "";
			return;
		}
		if(empty($params['gender']) || trim($params['gender']) == ''){
			echo "";
			return;
		}
		if(empty($params['avatar_large']) || trim($params['avatar_large']) == ''){
			echo "";
			return;
		}
		if(empty($params['token']) || trim($params['token']) == ''){
			echo "";
			return;
		}
		$id = trim($params['id']);
		$screen_name = trim($params['screen_name']);
		global $WB_CITY_ARR;
		$province = $WB_CITY_ARR[trim($params['province'])]["province"];
		$city = $WB_CITY_ARR[trim($params['province'])]["city"][trim($params['city'])];
		$gender = 1;
		if(trim($params['gender']) == 'f'){
			$gender = 2;
		}
		if(empty($params['connfrom']) || trim($params['connfrom']) == ''){
			$connfrom = 902;
		}else{
			if(trim($params['connfrom']) == 1){
				$connfrom = 902;
			}else if(trim($params['connfrom']) == 2){
				$connfrom = 802;
			}else{
				$connfrom = 902;
			}
		}
		$avatar_large = trim($params['avatar_large']);
		$email = 'weibo_'.$id.'@weibo.com';
		
		$token = trim($params['token']);
		$token_secret = trim($params['token_secret']);

		$emailInfo = $this->user->getRow(array('email' => $email));
		if(!$emailInfo){
			exit;
		}
		$userinfo = array();
		
		//判断tb_user表中是否存在该用户Email
		if (isset($emailInfo['id']) && $emailInfo['id']){
			$userinfo["userid"] = $emailInfo['id'];
			$userinfo["gender"] = $emailInfo['sex'];
		}else{
			$upDir = ".././img/user/";
			$monDir = $upDir.date("Ym");
			if(!is_dir($monDir)){
				mkdir($monDir , 0777);
			}
			$dayDir = $monDir."/".date("d");
			if(!is_dir($dayDir)){
				mkdir($dayDir , 0777);
			}
			$hourDir = $dayDir."/".date("d");
			if(!is_dir($hourDir)){
				mkdir($hourDir , 0777);
			}
			$hourDir = $hourDir."/".time().".png";
			$imgStr = file_get_contents($avatar_large);
			$fp = fopen($hourDir,'wb');   
			if(fwrite($fp, $imgStr)){
				$head_pic = $hourDir;
			}else{
				if($gender == 1){
					$head_pic = './img/user/default/male.jpg';
				}else{
					$head_pic = './img/user/default/female.jpg';
				}
				echo "no";
			}
			//随机生成8位数的密码[明文]
			$chars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k","l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v","w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G","H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R","S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2","3", "4", "5", "6", "7", "8", "9");
			$charsLen = count($chars) - 1;
			shuffle($chars);
			$psw = "";
			for ($i=0; $i<8; $i++){
				$psw .= $chars[mt_rand(0, $charsLen)];
			}
			//添加新浪微博用户信息到tb_user表
			$adduser = array(
				'username'=>$screen_name,
				'head_pic'=>$head_pic,
				'password'=>$psw,
				'email'=>$email,
				'sex'=>$gender,
				'city'=>$city,
				'province'=>$province,
				'time_created'=>time(),
				'log_time'=>0,
				'connfrom'=>$connfrom
			);
			$userid = $this->user->add($adduser , true);
			//添加新浪微博用户到tb_connect表
			$addconnect = array(
				'type'=>1,
				'uid'=>$userid,
				'connuid'=>$id,
				'connuname'=>$screen_name,
				'token'=>$token,
				'token_secret'=>$token_secret,
				'isbind'=>0,
				'issync'=>0,
				'createtime'=>time(),
				'updatetime'=>0
			);
			$result = $this->connect->add($addconnect , true);
			$userinfo["userid"] = $userid;
			$userinfo["gender"] = $gender;
		}
		//打印该用户的ID
		echo $this->customJsonEncode($userinfo);
	}
	
	/**
	 * 根据微博用户的性别返回评分搭配的数据[女8套,男4套]
	 * gender		性别[0--未知,1--男,2--女]默认:女
	 */
	public function dresslistAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$gender = $this->getIntParam('gender');
		$fields = $this->getParam("fields");
		if (!$gender){
			$gender = 2;
		}
		
		if($gender == 1){
			//$min = 9;
			//$max = 12;
			$arr_cat = array('3','6','9','12');
		}else{
			//$min = 1;
			//$max = 8;
			$arr_cat = array('1','2','4','5','7','8','10','11');
		}
		
		global $TRYOUT_IMG_URL,$recommend_tag_cat,$ITEM_TYPE;
		
		$dressArr = array();
		
		foreach ($arr_cat as $key => $value){
			$where = array();
			$where[] = "gender = $gender";
			$where[] = "cat_id = $value";
//			$time = strtotime(date("Y-m-d",strtotime("-30 day")));
//			$where[] = "time_created >= $time";
			$data = array();
			$fieldsAppDressManage = "id,pxid,pxtitle,px_img_url,description,gender,items_id,recommend";
			$fieldsAppDressManage = $this->mergeFields($fieldsAppDressManage, $fields); 
			$data = $this->appdressmange->listRandWithFields($fieldsAppDressManage,$where,1);
			if(!$data){
				exit;
			}
			if(count($data) == 1){
				$data[0]["px_img_url"] = $TRYOUT_IMG_URL.$data[0]["px_img_url"];
				$dressArr[] = $data[0];
			}
			unset($where);
			unset($data);
		}
		
		//随机评分单品
		//衣服[1],鞋子[2],包包[3],配饰[4]
		$myitemArr = array();
		foreach ($ITEM_TYPE as $key => $value){
			
			$where[] = "gender = $gender";
			$where[] = "item_type = $key";
			$where[] = "delstatus = 1";
//			$time = strtotime(date("Y-m-d",strtotime("-60 day")));
//			$where[] = "time_created >= $time";
			$fieldsRecommend = "id,itemid,title,price,details,recommendorder,recommenddate,recommendimgurl,recommendimgurlfile,ow,oh,tag_cat_id,gender";
			$data = $this->recommend->listRandWithFields($fieldsRecommend,$where,4);
			if(!$data){
				exit;
			}
			foreach ($data as $key => $value){
				$data[$key]["recommendimgurl"] = $TRYOUT_IMG_URL.getPropath($value["recommendimgurl"],196);
				$data[$key]["recommendimgurlfile"] = $TRYOUT_IMG_URL.$value["recommendimgurlfile"];
				
				$data[$key]["source_url"] = SOURCE_DOMAIN."?m=go&id=".$value["itemid"];
				$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,title,price,discount,img_url,ow,oh,summary,rank,view,favor,likenum,commnum";
				$myitem = $this->myitem->getRowWithFields($fieldsMyitem,$value["itemid"]);
				
				$data[$key]['width'] = 200;
				$data[$key]['height'] = floor($myitem["oh"]*(200/$myitem["ow"]));
			}
			$myitemArr[] = $data;
			unset($data);
			unset($where);
		}
		$arr = array();
		$arr["pinxiu"] = $dressArr;
		$arr["myitem"] = $myitemArr;
		unset($dressArr);
		unset($myitemArr);
		
		echo $this->customJsonEncode($arr);
	}
	
	/**
	 * 评测规则
	 * gender			用户性别[1--男,2--女]
	 * px_cateid		搭配风格ID
	 * figureid			用户选择的身材ID
	 */
	public function evaluatruleAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
				
		$gender = $this->getIntParam('gender');
		if (!$gender){
			echo "";
			return;
		}
		
		$px_cateid = $this->getIntParam('px_cateid');
		if (!$px_cateid){
			echo "";
			return;
		}
		
		$figureid = $this->getIntParam('figureid');
		if (!$figureid){
			echo "";
			return;
		}
		
		$arr = $this->getdress($gender,$px_cateid,$figureid);
	
		$data = array();
		$data["px_title"] = $arr[0]["title"];
		
		$data["px_explain"] = $arr[0]["explain"][1];
		
		$rand = rand(1,3);
		$data["px_style"] = $arr[0]["style"][$rand];
		
		$count_star = count($arr[0]["star"]);
		$rand_star = rand(1,$count_star);
		$data["mx_name"] = $arr[0]["star"][$rand_star]["name"];
		$data["mx_img_url"] = $arr[0]["star"][$rand_star]["avatar"];
		
		$data["px_group"] = $arr[0]["group"][1];
		
		//记录评测结果
		$arr_info = array();
		$arr_info["mx_name"] = $data["mx_name"];
		$arr_info["mx_img_url"] = $data["mx_img_url"];
		$arr_info["px_title"] = $data["px_title"];
		$arr_info["px_explain"] = $data["px_explain"];
		$arr_info["px_style"] = $data["px_style"];
		$arr_info["px_group"] = $data["px_group"];
		$addevaltake = "";
		
		$dress_info = $this->customJsonEncode($arr_info);
		$addevaltake = array(
			'userid'=>time(),
			'take_type'=>501,
			'evaltake'=>$px_cateid,
			'dress_info'=>$dress_info,
			'time_created'=>time()
		);
		$result = $this->evaltake->add($addevaltake , true);
		unset($arr_info , $addevaltake);
		$data["evaltakeid"] = $result;
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 修改保存用户评测的用户ID
	 * evid			评测保存记录ID
	 * uid			用户ID
	 * px_cateid	评测出来的搭配风格ID
	 */
	public function getEvaltakeAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
				
		$evid = $this->getIntParam('evid');
		if (!$evid){
			echo "";
			return;
		}
		
		$uid = $this->getIntParam('uid');
		if (!$uid){
			echo "";
			return;
		}
		
		$px_cateid = $this->getIntParam('px_cateid');
		if (!$px_cateid){
			echo "";
			return;
		}
		
		$rowU = $this->user->getRow($uid);
		if(!isset($rowU)){
			echo "";
			return;
		}
		$this->user->update(array('stgle' => $px_cateid) , $uid);
		
		$row = $this->evaltake->getRow($evid);
		if(!isset($row)){
			echo "";
			return;
		}
		$result = $this->evaltake->update(array('userid' => $uid) , $evid);
		echo $result;
	}
	
	/***
	 * 测试规则
	 * $sex			性别
	 * $px_cat		风格
	 * $statu		身材
	 */
	protected function getdress($sex,$px_cat,$statu){		
		$arr_sex[1]  = array(
			'px' => array(
				1	=>  array( //浓缩精华型
					array('name' => '曾志伟' , 'wbname' => '曾志偉' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zengzhiwei.jpg'),
					array('name' => '蔡康永' , 'wbname' => '蔡康永' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/caikangyong.jpg')
				),
				2	=>  array( //高大威猛型
					array('name' => '周润发' , 'wbname' => '周潤發' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhourunfa.jpg'),
					array('name' => '胡兵' , 'wbname' => '胡兵' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/hubing.jpg')
				),
				3	=>  array( //匀称型男型
					array('name' => '古天乐' , 'wbname' => '古天樂' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/gutianle.jpg'),
					array('name' => '彭于晏' , 'wbname' => '彭于晏' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/pengyuyan.jpg')
				),
				4	=>  array( //瘦瘦高高型
					array('name' => '郭品超' , 'wbname' => '郭品超' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/guopinchao.jpg'),
					array('name' => '吴彦祖' , 'wbname' => '吳彥祖' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/wuyanzu.jpg')
				)
			),
			'statu' => array(
				6	=>	array(
					'title'		=>	'运动休闲',
					'explain'	=>	array(
										1	=>	'有个性，有活力，不随波逐流，欣赏自己的生活态度'
									),
					'style'		=>	array(
										1	=>	'注重生活质量的你，跳脱呆版的刻板印象，腔调闲暇生活的重要的价值观，通过轻松的风格与舒适低调的奢华，体现独特魅力，导致了你对休闲装的高要求。',
										2	=>	'充满了无尽的活力，不喜欢被拘束着过日子，喜欢享受运动和阳光的感觉，拥有自己的个性，从不随波逐流是你一贯的作风，懂得如何生活的男人。',
										3	=>	'健康性感的充满活力的干干净净的一副运动休闲打扮的老爱裸上半身的对裸下半身也不太在乎的大大咧咧的不拘小节的的阳光大男生！'
									),
					'group'		=>	array(
										1	=>	'男人的衣服并不须要很时尚或很流行，但是要注意简洁大方，颜色沉稳，风格流畅就好，但是面料上需要讲究，这样可以提升你对生活品质和品味的最求。'
									),
					'star'		=>	array(
										1	=>	array('name' => 'Rain' , 'wbname' => 'Rain' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/Rain.jpg'),
										2	=>	array('name' => '古天乐' , 'wbname' => '古天乐' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/gutianle.jpg'),
										3	=>	array('name' => '汤姆克鲁斯' , 'wbname' => '汤姆克鲁斯' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/tmkls.jpg')
									)
				),
				12	=>	array(
					'title'		=>	'英伦风范',
					'explain'	=>	array(
										1	=>	'绅士又不拘束，穿梭在大都市的最佳格调'
									),
					'style'		=>	array(
										1	=>	'考究的着装，文雅的举止，尊重女性，尊重人格，具有绅士风范的你，对生活质量的追求与建造。彰显男人的刚毅坚韧、含蓄深沉。对你来说英伦风格一定是你的首选。',
										2	=>	'英伦风格没有一个准确的定义，你却能准确的抓住英伦风格的要点，良好的剪裁以及简洁修身的设计，体现绅士风度与贵族气质，个别带有欧洲学院风的味道。',
										3	=>	'你最前卫，也最保守，叛逆，混搭，年轻，一点点颓废，一点点摇滚，伴随着时装史上无数富有创意的时刻英伦时尚就是前卫的代名词。'
									),
					'group'		=>	array(
										1	=>	'想要让自己看起来很英伦，可以选择双排扣，格纹衬衣，大围巾，黑色休闲裤，款式简单的休闲皮鞋，但是整体风格要保持简约，太过于反复的搭配会大大的减分。'
									),
					'star'		=>	array(
										1	=>	array('name' => '贝克汉姆' , 'wbname' => '贝克汉姆' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/bkhm.jpg'),
										2	=>	array('name' => '裘德洛' , 'wbname' => '裘德洛' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/qiudeluo.jpg'),
										3	=>	array('name' => '吴彦祖' , 'wbname' => '吴彦祖' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/wuyanzu.jpg')
									)
				),
				9	=>	array(
					'title'		=>	'品味it男',
					'explain'	=>	array(
										1	=>	'成功的低调掩饰不住你的锋芒，这样的穿着最舒服谁说不是'
									),
					'style'		=>	array(
										1	=>	'想做一个有品味的it男摆脱民工的称号吗？那就不要再一味沉浸在自己的数码小世界，在一条盲目的不归路上雄赳赳的走下去最终结局只能是孤芳自赏。',
										2	=>	'认真、踏实，低调，不修边幅，让你对服装的要求并不是那么的高，何不让自己来一个彻头彻尾的大变身呢，给自己增添几件新衣服吧，给你身边的人一个惊喜大转变。',
										3	=>	'每个IT男都有一颗心律不齐然而闷骚的心，赶快爆发自己这颗闷骚的心，展示自己最潮最in的一面，这样不仅会让别人对你刮目相看，还能增加不少女人缘哦。'
									),
					'group'		=>	array(
										1	=>	'做个有品味的it男其实很简单，因为你的上升空间非常的打，给自己配上一条大围巾，换上一些修身的衣服，戴上大墨镜，瞬间你绝对就是最潮的it男。'
									),
					'star'		=>	array(
										1	=>	array('name' => '李连杰' , 'wbname' => '李连杰' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/lilianjie.jpg'),
										2	=>	array('name' => '马克·扎克伯格' , 'wbname' => '马克·扎克伯格' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/mkzkgb.jpg'),
										3	=>	array('name' => '乔布斯' , 'wbname' => '乔布斯' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/qiaobusi.jpg')
									)
				),
				3	=>	array(
					'title'		=>	'正统高贵',
					'explain'	=>	array(
										1	=>	'气质独特，商务场上百战百胜的狠角色'
									),
					'style'		=>	array(
										1	=>	'事业成功的你，注重形象，拥有迷人的超然时尚品味，正装绝对是你出席重大场合的唯一选择，在你眼里正装是男人的标志，是一种品味的体现。',
										2	=>	'对生活品位和潮流品牌认知的追求不亚于女生。个性的装扮，自我的主张，独特的品位，追逐最潮的时尚品味，专注的执着，做自己的风格。',
										3	=>	'品位可以不时髦但一定要出众，不是盲目的追随流行，追随带有当下最热元素的衣服。只有经典的才会将你本身的气质慢慢沉淀出来。'
									),
					'group'		=>	array(
										1	=>	'正装的重点是在于细节部分的体现，可以选择一件双翻袖的衬衣，配上一副精致又别致的袖扣，领带选择不要太夸张的图案即可，最重要的就是配上一双足够华丽的皮鞋。'
									),
					'star'		=>	array(
										1	=>	array('name' => '刘德华' , 'wbname' => '刘德华' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/liudehua.jpg'),
										2	=>	array('name' => '乔治克鲁尼' , 'wbname' => '乔治克鲁尼' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/qzkln.jpg'),
										3	=>	array('name' => '周润发' , 'wbname' => '周润发' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhourunfa.jpg')
									)
				)
			)
		);
		$arr_sex[2]  = array(
			'px' => array(
				1	=>  array( //气质丰腴型
					array('name' => '范冰冰' , 'wbname' => '范冰冰' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/fanbingbing.jpg'),
					array('name' => '刘嘉玲' , 'wbname' => '劉嘉玲' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/liujialing.jpg')
				),
				2	=>  array( //纤瘦骨干型
					array('name' => '张曼玉' , 'wbname' => '張曼玉' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhangmanyu.jpg'),
					array('name' => '郑秀文' , 'wbname' => '鄭秀雯' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhengxiuwen.jpg')
				),
				3	=>  array( //娇小可爱型
					array('name' => '张韶涵' , 'wbname' => '張韶涵' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhangyunhan.jpg'),
					array('name' => '周迅' , 'wbname' => '周迅' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhouxun.jpg')
				),
				4	=>  array( //性感高挑型
					array('name' => '林志玲' , 'wbname' => '林志玲' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/linzhiling.jpg'),
					array('name' => 'angelababy' , 'wbname' => 'angelababy' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/angelababy.jpg')
				),
				5	=>  array( //柔美线条型
					array('name' => '桂纶镁' , 'wbname' => '桂綸鎂' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/guilunmei.jpg'),
					array('name' => '舒淇' , 'wbname' => '舒淇' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/shuqi.jpg')
				),
				6	=>  array( //太平公主型
					array('name' => '吴君如' , 'wbname' => '吳君如' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/wujunru.jpg'),
					array('name' => '田馥甄' , 'wbname' => '田馥甄' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/tianfuhen.jpg')
				)
			),
			'statu' => array(
				1	=>	array(
					'title'		=>	'时尚摩登',
					'explain'	=>	array(
										1	=>	'白领职场中的时尚典范'
									),
					'style'		=>	array(
										1	=>	'最具个性创意，强调设计创新，前卫的风格演绎，颇具时尚感，又能完美的托衬自己的气质，不管哪种装扮都能穿出时尚的味道，呈现不同寻常的潮流。',
										2	=>	'各种风格，元素搭配到极致，多元素的时尚让人目不暇接，最让人赞叹的是你对于时尚总是有源源不断的灵感，总是给大家一种新的视觉感受。',
										3	=>	'优雅，时尚，精致，你就是所谓的女人中的时尚典范，在瞬息万变的时尚潮流中，不仅不会迷失自己的方向，还能找到属于自己的风格。'
									),
					'group'		=>	array(
										1	=>	'想要让自己更具摩登魅力，选择紧身的皮装或者黑色是绝对不会错的，不要选择碎花以及宽松公主风格的服装，那样会破坏你的整体风格哦。'
									),
					'star'		=>	array(
										1	=>	array('name' => '林志玲' , 'wbname' => '林志玲' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/linzhiling.jpg'),
										2	=>	array('name' => '徐濠萦' , 'wbname' => '徐濠萦' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/xuhaosu.jpg'),
										3	=>	array('name' => '章子怡' , 'wbname' => '章子怡' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhangziyi.jpg')
									)
				),
				4	=>	array(
					'title'		=>	'气场女王',
					'explain'	=>	array(
										1	=>	'商务活动中的领袖或精英'
									),
					'style'		=>	array(
										1	=>	'让人不得不为之折服和敬仰，拥有强大气场的你，即使着装不够华丽，妆容不够完美，也能让他人崇拜，活出你的高姿态，凝聚你的强气场，做真正的气场女王，让别人羡慕嫉妒恨！',
										2	=>	'张扬醒目的风格选择带来无与伦比的强大气场，追求上乘的丝质面料，简洁的服装廓形，成为最具有型格的女魔头人物，穿着搭配的气场绝对是效果出众。 ',
										3	=>	'喜欢都市感时装系列。精致的风格打造隐隐的向人们诉说着自己的人身阅历和丰富的职场经验的故事。而这些融合了众多元素的细节都通过现代感的方式得以完美重塑。'
									),
					'group'		=>	array(
										1	=>	'想要具有气场，重点在于大气以及霸气，要hold住全场，皮草是必不可少的，再者就是大面积的色块拼接，不要搭配卡通类型的服装，那样会适得其反。'
									),
					'star'		=>	array(
										1	=>	array('name' => '安吉丽娜朱莉' , 'wbname' => '安吉丽娜朱莉' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/ajlnzl.jpg'),
										2	=>	array('name' => '范冰冰' , 'wbname' => '范冰冰' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/fanbingbing.jpg'),
										3	=>	array('name' => '张曼玉' , 'wbname' => '张曼玉' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhagnmanyu.jpg')
									)
				),
				7	=>	array(
					'title'		=>	'清新森系',
					'explain'	=>	array(
										1	=>	'日常生活中的崇尚自然，不随波逐流'
									),
					'style'		=>	array(
										1	=>	'偏爱多层次民族风，崇尚简单，不随意追流行疯时尚，不追求品牌，生活态度悠闲，穿着有如走出森林的自然风格，天真，自然，不做作，简简单单就是你。',
										2	=>	'追求自然清新的风格、简洁、利落、清纯，从不娇柔做作，喜欢所有文艺的东西，从不跟从大潮流走，喜欢属于自己的风格，陶醉在属于自己的小世界中。',
										3	=>	'以清新唯美、随意风格见长，喜欢被大家认定为自己是小清新，憧憬着美好的意境，秉承淡雅、自然、朴实、超脱、静谧，不娇柔做作的特点而存在着。'
									),
					'group'		=>	array(
										1	=>	'民族风格的东西是增加自然气质的必备品，但是物极必反，不要过于最求民族感，清新的关键在于自然，一切以自然为中心，那样就事半功倍了。'
									),
					'star'		=>	array(
										1	=>	array('name' => '陈绮贞' , 'wbname' => '陈绮贞' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/cheqizhen.jpg'),
										2	=>	array('name' => '田馥甄' , 'wbname' => '田馥甄' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/tianfuzhen.jpg')
									)
				),
				11	=>	array(
					'title'		=>	'甜美可爱',
					'explain'	=>	array(
										1	=>	'无论什么场合，都能让人感到亲切舒服'
									),
					'style'		=>	array(
										1	=>	'清纯甜美的装扮瞬间迷惑对方的心，美美有暖意。甜美，多变，夺目，这是你对着装的定义，渴望抓住众人的眼光，让生活变的更加精彩。',
										2	=>	'素以甜美风示人，甜甜的笑容及服饰搭配深入人心，你的穿着非常适合各位模仿参考哦！一身多彩淑女可爱的打扮永远不会单调，尽显淑女的端庄甜美。',
										3	=>	'你的可爱让时尚圈刮起一股名媛淑女可爱风，将各种优雅婉约一网打尽，喜欢用一些清爽而充满活力的粉嫩色彩来召唤春天的来临，让你身边的人感到有你就好比春天的环绕。'
									),
					'group'		=>	array(
										1	=>	'如何抓住众人的眼球，那么选择甜美可爱的装扮那就是不会错的啦，千万要记住，约会的时候不要穿着中性装和比较正式的服装，那样会压抑现场的气氛。'
									),
					'star'		=>	array(
										1	=>	array('name' => 'angelababy' , 'wbname' => 'angelababy' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/angelababy.jpg'),
										2	=>	array('name' => 'Leighton Meester' , 'wbname' => 'Leighton Meester' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/LeightonMeester.jpg')
									)
				),
				2	=>	array(
					'title'		=>	'热辣迷人',
					'explain'	=>	array(
										1	=>	'你是男人的杀手，也会让女伴们焕发活力'
									),
					'style'		=>	array(
										1	=>	'派对就好比是你的战场，做派对女王，在派对上大放异彩，成为瞩目焦点，每一次都是那么的重要，从服装到妆容都要精益求精，不容的有半点的瑕疵。',
										2	=>	'懂得如何运动自己的迷人气质，在搭配上永远是那么的注重细节，总是能抓准要点给身边的男人来一个致命的袭击，让他们统统拜倒在你性感热辣之下。',
										3	=>	'轻薄的质地、大胆的裁剪、绚丽的纹理无不让你为之心动，缠绕在身体的性感，优雅而不失青春气息，最能突出你注重精致的心思，让你看上去是个制造情趣的高手。'
									),
					'group'		=>	array(
										1	=>	'想要让自己充满激情？重点就是要出挑要够另类，所以可以选择一些比较夸张的配饰作为点缀，小小的几件配饰可以改变整体的风格，细节的把握也是相当的重要。'
									),
					'star'		=>	array(
										1	=>	array('name' => 'lady gaga' , 'wbname' => 'lady gaga' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/ladygaga.jpg'),
										2	=>	array('name' => '徐熙娣' , 'wbname' => '徐熙娣' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/xuxidi.jpg'),
										3	=>	array('name' => '张柏芝' , 'wbname' => '张柏芝' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/zhangbozhi.jpg')
									)
				),
				5	=>	array(
					'title'		=>	'派对女王',
					'explain'	=>	array(
										1	=>	'气质与众不同，那些隆重的款式在你身上恰如其分'
									),
					'style'		=>	array(
										1	=>	'在派对聚会越来越多姿多彩的今天，白天美人与黑夜美人的身份交替，黑夜就是你的白天，爱派对如命的你，最求个性，时尚，另类，高贵，极具动感妩媚。',
										2	=>	'一场场派对接踵而至，你却能在这个美女如云的盛宴里成为众人瞩目的焦点，因为你懂得要想胜出，就必须拥有别人没有的能抓人眼球的创意搭配。',
										3	=>	'派对让你热血沸腾，各种聚会接踵而至，要想成为黑夜Party里那颗夜明珠，除了敢秀，也需要搭配绝招和绝妙单品，你能成功掌握这些秘籍，成为真正的Party女王。'
									),
					'group'		=>	array(
										1	=>	'觉得自己还不够热辣吗？教你一招，上身可露可不露，重要的是不能别阻碍了胳膊的伸展，下身选择超短裙、热裤或者勾勒曲线完美的贴身牛仔裤，说不定街上的男生会对你吹口哨哦。'
									),
					'star'		=>	array(
										1	=>	array('name' => 'Blake Liveley' , 'wbname' => 'Blake Liveley' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/BlakeLiveley.jpg'),
										2	=>	array('name' => '蔡依林' , 'wbname' => '蔡依林' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/caiyilin.jpg')
									)
				),
				10	=>	array(
					'title'		=>	'中性简约',
					'explain'	=>	array(
										1	=>	'休闲是你的style，简单既是美，也是永恒'
									),
					'style'		=>	array(
										1	=>	'时代的改变让中性装成为女性的另一种选择，中性干练是你的态度，追求一种时尚、全新的心境，反映在服饰观念上，无论任何场合，你对中性风格的热爱都是那么的执着。',
										2	=>	'喜欢反其道而行之的中性装扮，硬朗的服装与柔美的女性本质相结合之后却不显突兀，反而让女人味明显突出，越发吸引眼球，更加有一种自然纯正的美感。',
										3	=>	'喜欢让自己有一头流行的硬朗短发，穿上了一身型格打扮，中性简约是你对服装的一种追求，但是你却非常懂得如何展示真正女人的一面，知道如何才能让自己更女人。'
									),
					'group'		=>	array(
										1	=>	'中性不代表不女人，简单干练是中性的灵魂，要把握还中性的尺度，可以选择干练的小西装和紧身裤作为搭配，最好不要在身上出现三个以上的颜色，反复的色彩让人感到视觉疲劳。'
									),
					'star'		=>	array(
										1	=>	array('name' => '桂纶镁' , 'wbname' => '桂纶镁' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/guilunmei.jpg')
									)
				),
				8	=>	array(
					'title'		=>	'淑女百搭',
					'explain'	=>	array(
										1	=>	'你是闺蜜中最受人喜爱的，着装风格恰如其分，百搭得体'
									),
					'style'		=>	array(
										1	=>	'你奉行时尚、清新、简洁、大方的现代城市风格,给人以亲切的感觉。喜欢韩日风格等现行流行的女装风格，偏于可爱，淑女，优雅，端庄。',
										2	=>	'喜欢把自己打扮的优雅的淑女、端庄、大气，既有成熟女性的优雅干练，又不失年轻女孩的甜美娇俏，你懂得如何把握住细节的表现和重点。',
										3	=>	'带点甜美与娇嫩、加点性感与辛辣、添点时尚与高雅，就能打造出最平实美丽的，华丽的淑女风格就是要结合所有可以发挥的东西才能表现出来。'
									),
					'group'		=>	array(
										1	=>	'让自己变的更淑女，就要以轻松舒适为主，高跟鞋或者卡哇伊的小短靴，怎样女人怎样穿，切记不要让自己气场过盛，这样无形中会增加周围人的压力。'
									),
					'star'		=>	array(
										1	=>	array('name' => '大S' , 'wbname' => '大S' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/das.jpg'),
										2	=>	array('name' => '宋慧乔' , 'wbname' => '宋慧乔' , 'wbuid' => '' , 'avatar' => 'http://apptest.tuolar.com/iphoneAppwfs/images/star/songhuiqiao.jpg')
									)
				)
			)
		);
		
		$rand = rand(0,1);
		$arr = array();
		//$arr[0] = $arr_sex[$sex]['px'][$statu][$rand];		
		$arr[0] = $arr_sex[$sex]['statu'][$px_cat];
		return $arr;
	}
	
	/**
	 * 风格专题的单品信息列表
	 * type			风格分类ID[不可以为空]	气场女王[4],职场摩登[1],清新森系[7],休闲星期五[10],约会达人[11],夜场秀[2],闺蜜逛街[8],派对女王[5],男士正装[3],休闲格调[6],品位IT男[9],英伦风范[12]
	 * page			页码[默认:0或者1]
	 */
	public function styleitemAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$type = $this->getIntParam('type');
		if (!$type){
			echo "";
			return;
		}
		
		$where[] 		= "delstatus = 1";
		$where[] 		= "LOCATE(',$type,',tag_cat_id) > 0";
		
		$count		= $this->recommend->listCount($where);
		$pageSize	= 12;
		$fieldsRecommend = "id,itemid,title,price,details,recommendorder,recommenddate,recommendimgurl,recommendimgurlfile,ow,oh,gender,item_type";
		
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->recommend->listPageWithFields($fieldsRecommend, $where, 'id desc', $pageSize, $pageId);
		if(!$data){
			exit;
		}
		$pagesum = $this->getIntParam('page');
		if($pagesum > $pageId){
			echo "";exit;
		}
		
		$arr_itemid = array();
		foreach ($data as $key_id => $value_id){
			$arr_itemid[] = $value_id["itemid"];
		}
		$itemid 	= '';
		$arr_itemid 	= array_unique($arr_itemid);
		$itemid 	= implode(',' , $arr_itemid);
		$itemid   	= trim($itemid , ',');
		
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,bid,pid,type,tb_fav,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,rank,view,favor,likenum,commnum";
		$itemlist = $this->myitem->listAllWithFields($fieldsMyitem, "id in ($itemid)","id desc");
		if(!$itemlist){
			exit;
		}
		unset($itemid);
		global $TRYOUT_IMG_URL,$BUY_URL;
		foreach ($data as $key => $value) {
			$data[$key]["source_url"] = SOURCE_DOMAIN."?m=go&id=".$value["itemid"];
			$data[$key]["recommendimgurl"] = $TRYOUT_IMG_URL.getPropath($value["recommendimgurl"],196);
			$data[$key]["recommendimgurlfile"] = $TRYOUT_IMG_URL.$value["recommendimgurl"];
			foreach ($itemlist as $key_item => $value_item){
				if($value["itemid"] == $value_item["id"]){
					$data[$key]["likenum"] = $value_item["likenum"];
					$data[$key]["commnum"] = $value_item["commnum"];
					$data[$key]['width'] = 200;
					$data[$key]['height'] = floor($value["oh"]*(200/$value["ow"]));
				}
			}			
		}
		unset($itemlist);
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 风格专题的单个单品信息
	 * id			推荐单品ID
	 */
	public function myiteminfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam('id');
		if (!$id){
			echo "";
			return;
		}
		global $TRYOUT_IMG_URL;
		$row = $this->recommend->getRow($id);
		if(!$row){
			exit;
		}
		if ($row){
			$row["source_url"] = SOURCE_DOMAIN."?m=go&id=".$row["itemid"];
			$row["recommendimgurl"] = $TRYOUT_IMG_URL.getPropath($row["recommendimgurl"],196);
			$row["recommendimgurlfile"] = $TRYOUT_IMG_URL.$row["recommendimgurl"];
			
			$myitem = $this->myitem->getRow($row["itemid"]);
			if ($myitem){
				$row["likenum"] = $myitem["likenum"];
			}else{
				$row["likenum"] = rand(1,10);
			}
		}
		echo $this->customJsonEncode($row);
	}
	
	/**
	 * 一周的推荐搭配
	 * gender			性别[0--未知,1--男,2--女]默认:女
	 * page				页码[默认:0或者1]
	 */
	public function recommenddressmangeAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$gender = $this->getIntParam('gender');
		if (!$gender){
			$gender = 2;
		}
		
		$where[] 		= "delstatus = 0";
//		$where[] 		= "pxid != 0";
		$where[] 		= "gender = $gender";
//		$time = strtotime(date("Y-m-d",strtotime("-$day day")));
//		$where[] = "recommend >= $time";
		
		$count		= $this->appdressmange->listCount($where);
		$pageSize	= 10;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->appdressmange->listPage($where, 'recommend desc', $pageSize, $pageId);
		if(!$data){
			exit;
		}
		global $TRYOUT_IMG_URL;
		foreach ($data as $key => $value) {
			$data[$key]["px_img_url"] = $TRYOUT_IMG_URL.$value["px_img_url"];
		}
		
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 推荐单品
	 * gender			性别[0--未知,1--男,2--女]默认:女
	 * pxid				用户的所属风格分类
	 * page				页码[默认:0或者1]
	 */
	public function recommendmyitemsAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$gender = $this->getIntParam('gender');
		$otherFields = $this->getParam("fields");
		if (!$gender){
			$gender = 2;
		}
		$pxid = $this->getIntParam('pxid');
		if (!$pxid){
			echo "";
			return;
		}
		
		global $TRYOUT_IMG_URL;
		$where = array();
		
		//用户测评的所属的风格类型
		if ($pxid){
			$where[] 		= "((LOCATE(',$pxid,',tag_cat_id) > 0) or (tag_cat_id=$pxid))";
		}
		
		$where[] 		= "delstatus = 1";
		$where[] 		= "gender = $gender";
//		$time = strtotime(date("Y-m-d",strtotime("-60 day")));
//		$where[] = "recommenddate > $time";
		
		$data = array();
		$pageId = $this->getIntParam['page'];
		if(empty($pageId) || trim($pageId) == '' || $pageId === 0 || $pageId === 1){
			$timew = strtotime(date("Y-m-d",time()));
			$wherer = array();
			$wherer = $where;
			$wherer[] = "recommenddate >= $timew";

			$data = $this->recommend->listAll($wherer);
			unset($wherer);
		}
		//$timew = strtotime(date("Y-m-d",time()));
		//$where[] = "recommenddate < $timew";
		
		$i = count($data);
		$count		= $this->recommend->listCount($where);
		if($i < 10){
			$pageSize	= 18-count($data);
		}else{
			$pageSize	= 18;
		}
		$fieldsRecommend = "id,itemid,title,price,details,recommendorder,recommenddate,recommendimgurl,recommendimgurlfile,ow,oh,gender,item_type";
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->recommend->listPageWithFields($fieldsRecommend, $where, 'id desc', $pageSize, $pageId);
		if(count($data) == 0){
			echo "";exit();
		}
		$pagesum = $this->getIntParam('page');
		if($pagesum > $pageId){
			echo "";exit;
		}
		
		$arr_itemid = array();
		foreach ($data as $key_id => $value_id){
			$arr_itemid[] = $value_id["itemid"];
		}
		$itemid 	= '';
		$arr_itemid 	= array_unique($arr_itemid);
		$itemid 	= implode(',' , $arr_itemid);
		$itemid   	= trim($itemid , ',');
		
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,summary,favor,likenum,commnum";
		$fieldsMyitem = $this->mergeFields($otherFields, $otherFields);
		
		$itemlist = $this->myitem->listAllWithFields($fieldsMyitem, "id in ($itemid)","id desc");
		unset($itemid);
		foreach ($data as $key => $value) {
			$data[$key]["source_url"] = SOURCE_DOMAIN."?m=go&id=".$value["itemid"];
			$data[$key]["recommendimgurl"] = $TRYOUT_IMG_URL.getPropath($value["recommendimgurl"],196);
			$data[$key]["recommendimgurlfile"] = $TRYOUT_IMG_URL.$value["recommendimgurl"];
			$data[$key]['width'] = 196;
			$data[$key]['height'] = floor($value["oh"]*(196/$value["ow"]));
			foreach ($itemlist as $key_item => $value_item){
				if($value["itemid"] == $value_item["id"]){
					$data[$key]["likenum"] = $value_item["likenum"];
					$data[$key]["commnum"] = $value_item["commnum"];
				}
			}
			$i++;
			unset($key);
			unset($value);
		}
		unset($where);
		unset($itemlist);

		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 用户收藏单品信息列表
	 * uid				用户ID
	 * page				页码[默认:0或者1]
	 */
	public function myfavoritesAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$uid = $this->getIntParam('uid');
		if (!$uid){
			echo "";
			return;
		}
		
		$where[] 		= "userid = $uid";
		$count		= $this->favorites->listCount($where);
		$pageSize	= 12;
		$this->view->paging		= $this->getPaging($count, $pageSize, $pageId);
		$data	= $this->favorites->listPage($where, 'id desc', $pageSize, $pageId);
		if(!$data){
			exit;
		}
		global $TRYOUT_IMG_URL;
		
		foreach ($data as $key => $value){
			$item = array();
			
			$item = $this->recommend->getRow(array("id"=>$value["recommendid"]));
			if ($item){
				$item["source_url"] = SOURCE_DOMAIN."?m=go&id=".$item["itemid"];
				$item["recommendimgurl"] = $TRYOUT_IMG_URL.getPropath($item["recommendimgurl"],196);
				$item["recommendimgurlfile"] = $TRYOUT_IMG_URL.$item["recommendimgurl"];
				$fieldsMyitem = "id,uid,type,title,price,discount,img_url,ow,oh,summary,likenum,commnum";
				$myitem = $this->myitem->getRowWithFields($fieldsMyitem,$item["itemid"]);
				if ($myitem){
					$item["likenum"] = $myitem["likenum"];
				}else{
					$item["likenum"] = rand(1,10);
				}
				$data[$key]["item"] = $item;
			}
			unset($item);
		}
		echo $this->customJsonEncode($data);
	}
	
	/**
	 * 用户收藏推荐单品
	 * uid			用户ID
	 * rid			推荐单品ID
	 */
	public function addfavoriteAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$uid = $this->getIntParam('uid');
		if (!$uid){
			echo "";
			return;
		}
		
		$rid = $this->getIntParam('rid');
		if (!$rid){
			echo "";
			return;
		}
		
		$row = $this->favorites->getRow(array("userid"=>$uid,"recommendid"=>$rid));

		if (!$row){
			$arrfav = array(
				'userid'=>$uid,
				'recommendid'=>$rid,
				'time_created'=>time()
			);
			$result = $this->favorites->add($arrfav , true);
		}else{
			$result = $row["id"];
		}
		echo $result;
	}
	
	/**
	 * 用户删除我的收藏里的单品记录
	 * id			推荐单品的ID
	 */
	public function delmyfavoriteAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam('id');
		if (!$id){
			echo "";
			return;
		}
		
		$result = $this->favorites->delete("id=$id");
		echo $this->customJsonEncode($result);
	}
	
	/**
	 * 用户批量添加收藏推荐单品
	 * uid			用户ID
	 * filist		推荐单品ID,收藏时间[推荐单品1ID##收藏1时间@@推荐单品2ID##收藏时间2@@推荐单品3ID##收藏时间3@@]
	 */
	public function addfavoritesAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$userid = $this->getIntParam('uid');
		if (!$userid){
			echo "";
			return;
		}
		
		$filist = $this->getParam('filist');
		if (!$filist){
			echo "";
			return;
		}
		echo $filist;
		$strID = "";
		$fitem = explode("@@",$filist);
		foreach ($fitem as $key => $value){
			echo $value;
			$arr = array();
			$arr = explode("$$",$value);
			
			$rid = $arr[0];
			$tcreated = strtotime($arr[1]);
			
			$row = $this->favorites->getRow(array("userid"=>$userid,"recommendid"=>$rid));
			if(!$row){
				exit;
			}
			if (!$row){
				$arrfav = array(
					'userid'=>$userid,
					'recommendid'=>$rid,
					'time_created'=>$tcreated
				);
				$result = $this->favorites->add($arrfav , true);
			}else{
				$result = $row["id"];
			}
			$strID = $strID."$$".$result;
			unset($row);
			unset($arr);
			unset($key);
			unset($value);
		}
		echo $strID;
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

Config::extend('DressController', 'Controller');
