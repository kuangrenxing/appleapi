<?php
Globals::requireClass('Controller');

class UploadController extends Controller
{
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->config['layoutEnabled'] 	= false;
		$this->config['viewEnabled'] 	= false;
	}
	
	public function indexAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		Globals::requireClass('UploadFile');
		$upload = new UploadFile();
		$upload->allowExts = array("jpg" , "png" , "gif" , "jpeg" , "JPG" , "PNG" , "GIF" , "JPEG");
		$upDir = "./img/uimg/";
		
		$monDir = $upDir.date("Ym");
		if(!is_dir($monDir)){
			mkdir($monDir , 0777);
		}
		$dayDir = $monDir."/".date("d");
		if(!is_dir($dayDir)){
			mkdir($dayDir , 0777);
		}
		$hourDir = $dayDir."/".date("g");
		if(!is_dir($hourDir)){
			mkdir($hourDir , 0777);
		}
		$upload->savePath = $hourDir."/";
		
		//生成缩略图
		$upload->thumb = true;
		/*$upload->thumbMaxHeight = "300,200,90";
		$upload->thumbMaxWidth = "300,200,90";
		$upload->thumbSuffix = '_300,_200,_90';
		$upload->thumbMode = '3,0,1';*/
		$upload->thumbMaxHeight = "600,300,200,90";
		$upload->thumbMaxWidth = "600,300,200,90";
		$upload->thumbSuffix = '_600,_300,_200,_90';
		$upload->thumbMode = '3,3,0,1';
		
			
		if ($upload->upload()){
			$fileInfo = $upload->getUploadFileInfo();
			$form['pic'] = $fileInfo[0]['savepath'].$fileInfo[0]['savename'];
			$picArr = explode("." , $fileInfo[0]['savename']);
			$size = getimagesize(IMAGE_DOMAIN.$form['pic']);
			$s_str = $size[0]>$size[1]?'width':'height';
			$msg	= $form['pic'].";;".$s_str;
		}else {
			$msg = "0;;".$upload->getErrorMsg();
		}
		echo $msg;
	}
}