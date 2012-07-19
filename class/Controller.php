<?php

class Controller extends Config
{
	public static $defaultConfig = array(
		'viewEnabled' 		=> false,
		'viewDir'			=> null,
		'viewExtension'		=> 'phtml',
		'layoutEnabled'		=> false,
		'layoutName'		=> 'layout'
	);
	
	protected $controller;
	protected $action;
	protected $view;
	protected $layout;
	
	public static function runController($controller = null, $config = null)
	{
		if (is_null($controller))
			$controller = isset($_GET['m']) ? $_GET['m'] : (isset($_POST['m']) ? $_POST['m'] : null);
		
		if (!$controller)
			$controller = 'index';
		
		$name = Globals::firstToUpper($controller);
		
		Globals::requireController($name);
		$className = $name.'Controller';
		$controller = new $className($config);
		$controller->run();
	}
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		
		$className	= get_class($this);
		$controller	= substr($className, 0, strrpos($className, 'Controller'));
		
		$this->controller = Globals::firstToLower($controller);
	}
	
	public function run()
	{
		$action = $this->getParam('a');
		
		if (!$action)
			$action = 'index';
		
		$this->action = $action;
		$actionMethod = $action.'Action';
		
		if (method_exists($this, $actionMethod))
		{
			if ($this->config['viewEnabled'])
			{
				Globals::requireClass('View');
				
				$this->view = new View(array(
					'dir'		=> $this->config['viewDir'].'/'.$this->controller,
					'name'		=> $this->action,
					'extension'	=> $this->config['viewExtension']
				));
				
				if ($this->config['layoutEnabled'])
				{
					$this->layout = new View(array(
						'dir'		=> $this->config['viewDir'],
						'name'		=> $this->config['layoutName'],
						'extension'	=> $this->config['viewExtension']
					));
				}
				
				$this->$actionMethod();
				$this->out();
			}
			else $this->$actionMethod();
		}
		else Globals::error("action ($action) is undefined");
	}
	
	protected function out()
	{
		if ($this->config['viewEnabled'])
		{
			$scriptBase = $this->getScriptBase(true);
			
			if ($this->config['layoutEnabled'])
			{
				ob_start();
				$this->view->scriptBase = $scriptBase;
				$this->view->render();
				$this->layout->content = ob_get_contents();
				ob_end_clean();
				
				$this->layout->scriptBase = $scriptBase;
				$this->layout->render();
			}
			else
			{
				$this->view->scriptBase = $scriptBase;
				$this->view->render();
			}
		}
	}
	
	public function redirect($url)
	{
		header('Location: '.$url);
		die;
	}
	
	public function redirectToController($controller = null, $action = null, $params = null)
	{
		$uri		= './'.$this->getScriptName(true);
		$newParams	= array();
		
		if ($controller && $controller != 'index')
			$newParams[] = 'm='.$controller;
		
		if ($action && $action != 'index')
			$newParams[] = 'a='.$action;
		
		if (is_array($params))
		{
			foreach ($params as $key => $value)
				$newParams[] = $key.'='.$value;
		}
		
		if (count($newParams))
			$uri .= '?'.implode('&', $newParams);
		
		$this->redirect($uri);
	}
	
	public function redirectToAction($action = null, $params = null)
	{
		$this->redirectToController($this->controller, $action, $params);
	}
	
	public static function getBaseUrl($includePath = false)
	{
		$r = 'http://'.
				(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost').
				(isset($_SERVER['SERVER_PORT']) ? ($_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT']) : '');
		
		if ($includePath)
		{
			$path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
			
			if ($path != '/')
				$r .= $path;
		}
		
		return $r;
	}
	
	public static function getFullUrl()
	{
		return self::getBaseUrl().$_SERVER['REQUEST_URI'];
	}
	
	public function getScriptPath($rootToEmpty = false)
	{
		$dirname = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
		return $rootToEmpty && $dirname == '/' ? '' : $dirname;
	}
	
	public function getScriptName($indexToEmpty = false, $suffix = null)
	{
		$scriptName = basename($_SERVER['PHP_SELF'], $suffix);
		return $indexToEmpty && $scriptName == 'index.php' ? '' : $scriptName;
	}
	
	public function getScriptBase($indexToEmpty = false)
	{
		return $this->getScriptPath(true).'/'.$this->getScriptName($indexToEmpty);
	}
	
	public function getClientIp($returnLong = false)
	{
		return Globals::getClientIp($returnLong);
	}
	
	public function hasParam($name)
	{
		if (isset($_GET[$name]))
			return true;
		
		if (isset($_POST[$name]))
			return true;
		
		return false;
	}
	
	public function getParam($name)
	{
		if (isset($_POST[$name]))
			return $_POST[$name];
		
		if (isset($_GET[$name]))
			return $_GET[$name];
		
		return null;
	}
	
	public function getParams($names)
	{
		if (is_string($names))
			$names = explode(',', $names);
		
		if (is_array($names))
		{
			$r = array();
			
			foreach ($names as $name)
			{
				$name = trim($name);
				$r[$name] = $this->getParam($name);
			}
			
			return $r;
		}
	}
	
	public function getIntParam($name, $zeroToNull = false)
	{
		$value = intval($this->getParam($name));
		
		if ($zeroToNull && !$value)
			$value = null;
		
		return $value;
	}
	
	public function getNonnegativeParam($name)
	{
		$value = intval($this->getParam($name));
		
		if ($value < 0)
			$value = 0;
		
		return $value;
	}
	
	public function getNonemptyParam($name, $displayName = null)
	{
		$value = trim($this->getParam($name));
		
		if (!strlen($value))
			Globals::error(($displayName ? $displayName : $name).' is empty');
		
		return $value;
	}
	
	public function getBaseUri($page = false)
	{
		$uri		= $this->getScriptBase(true);

		$params		= $page ? array_merge($_GET, $_POST) : $_GET;
		$paramsNew	= array();
		
		if ($this->controller != 'index')
			$paramsNew[] = 'm='.$this->controller;
		
		if ($this->action != 'index')
			$paramsNew[] = 'a='.$this->action;
		
		foreach ($params as $k => $v)
		{
			if ($k == 'm' || $k == 'a' || ($page && $k == 'page'))
				continue;
			
			if ($k == 'tags')
				$paramsNew[] = $k.'='.urlencode($v);
			else{
				if ($v)
					$paramsNew[] = $k.'='.$v;
			}
		}
		unset($k , $v);
		
		if ($page)
			$paramsNew[] = 'page=';

		$uri .= count($paramsNew) ? '?'.implode('&', $paramsNew) : null;
		
		return $uri;
	}
	
	public function getRewriteUri()
	{
		$url = $this->getFullUrl();

		//匹配出page
		$url = preg_replace('/(&|\?)page=[^&]+/', '' , $url);
		
		$url .= strpos($url , '?') === false ? "?page=" : "&page=";
	
		return $url;
	}
	
	public function getPaging($count, &$pageSize = null, &$pageId = null , $style = 1)
	{
		if ($pageSize <= 0)
			$pageSize = 20;
		
		if ($count <= 0)
		{
			$pageId = 1;
			return;
		}
		
		if (1 == $style)
			$pageColumn = 12;
		elseif (2 == $style)
			$pageColumn = 5;
		elseif (3 == $style)
			$pageColumn = 5;
		elseif (4 == $style)
			$pageColumn = 5;
			
		$pageNum= ceil($count / $pageSize);
		$pageId	= $this->getIntParam('page');
		
		if ($pageId < 1)
			$pageId = 1;
		else if ($pageId > $pageNum)
			$pageId = $pageNum;
		
		$pageCount = $pageId == $pageNum ? $count - $pageSize * ($pageNum - 1) : $pageSize;
		
		if (REWRITE_OPEN)
			$baseUrl = $this->getRewriteUri();
		else
			$baseUrl = $this->getBaseUri(true);

		$baseNum 	= ceil($pageId/$pageColumn);
		
		if (1 == $style){
			$left		= ($pageId > 1) && ($baseNum > 1) ? '<a href="'.$baseUrl.'1">1</a> <a href="'.$baseUrl.($pageId - 1).'">上一页</a>' : '';
			$center		= '';
			$minNum 	= (($baseNum-1)*$pageColumn + 1);
			$maxNum		= min($pageNum , ($baseNum*$pageColumn));
			for ($n = $minNum ; $n <= $maxNum ; $n ++)
			{
				if ($maxNum == 1)
					continue;
				
				if ($n == $pageId)
					$center	.= '<span class="current"><strong>'.$n.'</strong></span>';
				else
					$center .= '<a href="'.$baseUrl.$n.'">'.$n.'</a>';
			}
	//		$right		= ($pageId < $pageNum) && ($baseNum*5 < $pageNum) ? '<a href="'.$baseUrl.($pageId + 1).'">››</a> <a href="'.$baseUrl.$pageNum.'">'.$pageNum.'</a>' : '';
			$right		= ($pageId < $pageNum) && ($baseNum*$pageColumn < $pageNum) ? '<a href="'.$baseUrl.($pageId + 1).'">下一页</a>' : '';
		}elseif (2 == $style){
			$left 		= '<ul class="clearfix">';
			$left		.= ($pageId > 1) && ($baseNum > 1) ? '<li class="lastPage"><a class="sprite01" href="'.$baseUrl.($pageId - 1).'">上一页</a></li><li><a href="'.$baseUrl.'1">1</a></li>' : '';
			$center		= '';
			$minNum 	= (($baseNum-1)*$pageColumn + 1);
			$maxNum		= min($pageNum , ($baseNum*$pageColumn));
			for ($n = $minNum ; $n <= $maxNum ; $n ++)
			{
				if ($maxNum == 1)
					continue;
				
				if ($n == $pageId)
					$center	.= '<li><a class="pageCur" page="'.$baseUrl.$n.'">'.$n.'</a></li>';
				else
					$center .= '<li><a href="'.$baseUrl.$n.'">'.$n.'</a></li>';
			}
			$right		= ($pageId < $pageNum) && ($baseNum*$pageColumn < $pageNum) ? '<li class="setBg"><span>...</span></li><li class="nextPage"><a class="sprite01" href="'.$baseUrl.($pageId + 1).'">下一页</a></li>' : '';
			$right .= '</ul>';
		}elseif(3 == $style){
			$left		= ($pageId > 1) && ($baseNum > 1) ? '<a href="'.$baseUrl.'1">1</a> <a href="'.$baseUrl.($pageId - 1).'" class="lastPage"><span class="sprite01">上一页</span></a>' : '';
			$center		= '';
			$minNum 	= (($baseNum-1)*$pageColumn + 1);
			$maxNum		= min($pageNum , ($baseNum*$pageColumn));
			for ($n = $minNum ; $n <= $maxNum ; $n ++)
			{
				if ($maxNum == 1)
					continue;
				
				if ($n == $pageId)
					$center	.= '<a class="pageCur" href="javascript:void(0);">'.$n.'</a>';
				else
					$center .= '<a href="'.$baseUrl.$n.'">'.$n.'</a>';
			}
	//		$right		= ($pageId < $pageNum) && ($baseNum*5 < $pageNum) ? '<a href="'.$baseUrl.($pageId + 1).'">››</a> <a href="'.$baseUrl.$pageNum.'">'.$pageNum.'</a>' : '';
			$right		= ($pageId < $pageNum) && ($baseNum*$pageColumn < $pageNum) ? '<span class="setBg">...</span><a href="'.$baseUrl.($pageId + 1).'" class="sprite01 nextPage"><span class="sprite01">下一页</span></a>' : '';
		}elseif(4 == $style){
			$left		= ($pageId > 1) && ($baseNum > 1) ? '<a href="'.$baseUrl.'1">1</a> <a href="'.$baseUrl.($pageId - 1).'" class="next pngFix">上一页</a>' : '';
			$center		= '';
			$minNum 	= (($baseNum-1)*$pageColumn + 1);
			$maxNum		= min($pageNum , ($baseNum*$pageColumn));
			for ($n = $minNum ; $n <= $maxNum ; $n ++)
			{
				if ($maxNum == 1)
					continue;
				
				if ($n == $pageId)
					$center	.= '<a class="cur" href="javascript:void(0);">'.$n.'</a>';
				else
					$center .= '<a href="'.$baseUrl.$n.'">'.$n.'</a>';
			}
	
			$right		= ($pageId < $pageNum) && ($baseNum*$pageColumn < $pageNum) ? '<span>...</span><a href="'.$baseUrl.($pageId + 1).'" class="next pngFix">下一页</a>' : '';
		}
		
		return ''.($left && $right ? $left.' '.$center.' '.$right : $left.$center.$right);
	}
	
	
	/*
	 * 对字段进行merge，以$referFields为主，$fields中在$referFields中有的字段才输出。
	*/
	public function mergeFields($referFields,$fields)
	{
		if(!$referFields)
		{
			return null;
		}
		elseif(!$fields){
			return $referFields;
		}
		else
		{
			$newArr = array();
			$referArr = explode(",", $referFields);
			$arr = explode(",", $fields);
			//echo $referFields,$fields;
			foreach ($referArr as $i=>$refer)
			{
				foreach($arr as $j=>$field)
				{
					if($refer == $field)
					{
						$newArr[] = $refer;
					}
				}
			}
				
			return implode(",",$newArr);
		}
	}
}

Controller::$defaultConfig['viewDir'] = Globals::$self->getConfig('viewDir');
