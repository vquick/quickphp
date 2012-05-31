<?php
/**
 * 框架系统类库
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Sys.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Sys
{
	/**
	 * QuickPHP 版本号
	 *
	 */
	const VERSION = '2.5.2';


	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	 * 得到 APP 的配置
	 *
	 */
	static public function getAppCfg(){
		return QP_Registry_Registry::getInstance()->get('APP_CFG');
	}

	/**
	 * 写日志,日志都写在 Application/Logs 目录下
	 *
	 * @param string $message 日志内容
	 * @param string $type 日志类型 'user':用户自定义日志 'app':框架的异常错误 'php':PHP的运行错误
	 */
	static public function log($message,$type='user'){
		$type = ucfirst(strtolower($type));
		// 得到日志目录
		$path = APPLICATION_PATH."/Data/Logs/{$type}/".date('Y-m');
		if(! file_exists($path)){
			mkdir($path,0777,true);
		}
		// 得到日志文件
		$file = $path.'/'.date('Y_m_d').'.log';
		$msg = "---------------[".date('Y-m-d H:i:s')."]---------------".PHP_EOL.$message.PHP_EOL;
		file_put_contents($file,$msg,FILE_APPEND);
	}

	/**
	 * 快速载入系统工具类库,即框架的子目录中的类库
	 *
	 * @example QP_Sys::load('config') 等价于 new QP_Config_Config()
	 * @param object $sysClass 类简名
	 * @param mixed $params 构造参数
	 */
	static public function load($sysClass,$params=null){
		$sysClass = ucfirst(strtolower($sysClass));
		$includeFile = QUICKPHP_PATH.'/'.$sysClass.'/'.$sysClass.'.php';
		if(!file_exists($includeFile)){
			throw new QP_Exception('类库不存在:'.$includeFile);
		}
		require_once $includeFile;
		$class = 'QP_'.$sysClass.'_'.$sysClass;
		if(! class_exists($class,false)){
			throw new QP_Exception('类未定义:'.$class.' 在文件:'.$includeFile);
		}
		return new $class($params);
	}

	/**
	 * 读取配置项
	 *
	 * @param string $cfgItem 配置项,如: "application"或"application.id"
	 * @return unknown
	 */
	static public function config($cfgItem){
		return self::load('config')->get($cfgItem);
	}

	/**
	 * 系统消息提示
	 *
	 * @param string $msg :消息文本
	 * @param string $url :将要跳转的URL  "":自动返回到上一页  "close":则关闭窗口
	 * @param $time $time :页面显示停留的时间,单位:秒,过了时间后自动跳转
	 */
	static public function msgbox($msg,$url='',$time=10){
		include QUICKPHP_PATH.'/Debug/Msgbox.php';
		exit(0);
	}


	/**
	 * 根据 QP_URL_METHOD 模式生成URL地址,不用担心站点路径问题
	 *
	 * @param string $controller 控制器 'this':表示当前控制器名称
	 * @param string $action  动作名 'this':表示当前动作名
	 * @param array|string $params 参数,可以是字符串或数组，具体如下：
	 *
	 * 例子:
	 * url('index','test', 'id=1&name=yuanwei'); // 字符串参数
	 * url('index','test', array('id'=>1,'name'=>'yuanwei')); // 数组参数
	 *
	 * 根据不同的 url_method 模式生成的 URL 如下:
	 * [standard] index.php/index/test/id/1/name/yuanwei
	 * [pathinfo] /?c=index&a=test&id=1&name=yuanwei
	 * [rewrite]  /index/test/id/1/name/yuanwei
	 * @return string
	 */
	static public function url($controller, $action=QP_Controller::DEFAULT_ACTION, $params=null)
	{
		$request = QP_Request::getInstance();
		// 根据不同的URL模式生成URL
		$appCfg = QP_Sys::getAppCfg();
		switch ($appCfg['url_method'])
		{
			// 常规模式
			case 'standard':
				// 如果是当前控制器
				if($controller == 'this'){
					$controller = $request->getGet('controller');
				}
				// 如果是当前动作
				if($action == 'this'){
					$action = $request->getGet('action');
				}
				// 连接控制器和动作并且把 'index.php' 从URL中去掉
				$url  = $_SERVER['SCRIPT_NAME'].'?'.QP_Request::C.'='.$controller;
				$url = str_replace('index.php', '', $url);
				$url .= '&'.QP_Request::A.'='.$action;
				// 连接参数
				if(is_array($params)){
					foreach ($params as $key=>$val){
						$url .= '&'.$key.'='.urlencode($val);
					}
				}elseif($params){
					$url .= '&'.$params;
				}
				break;

			//PATHINFO模式 或 REWRITE模式
			default:
				// 如果是当前控制器
				if($controller == 'this'){
					$controller = $request->getParam('controller');
				}
				// 如果是当前动作
				if($action == 'this'){
					$action = $request->getParam('action');
				}
				// 连接控制器和动作
				if ($appCfg['url_method'] == 'rewrite')
				{
					$url = dirname($_SERVER['SCRIPT_NAME']);
					if (strlen($url) == 1){
						$url = '/'.$controller;
					}else{
						$url .= '/'.$controller;
					}
				}else{
					$url = $_SERVER['SCRIPT_NAME'].'/'.$controller;
				}
				$url .= '/'.$action;
				// 连接参数
				if(is_array($params)){
					foreach ($params as $key=>$val){
						$url .= '/'.$key.'/'.urlencode($val);
					}
				}elseif($params){
					$url .= '/' . str_replace(array('&','='),array('/','/'),$params);
				}
				break;
		}
		return $url;
	}

	/**
	 * 引入 Zend Framework，这样在框架中就可以直接使用它的组件库了
	 *
	 * @param string $zfPath :绝对路径的 ZF 框架路径,如 "/data/ZendFramework/library" 注意目录名不要包含 "Zend"目录
	 */
	static public function zend($zfPath){
		set_include_path(implode(PATH_SEPARATOR, array(
		    $zfPath,
		    get_include_path(),
		)));
	}


	/**
	 * 注册自定义的装载器
	 *
	 * @param callback $callback :自定义回调，可以是以下三种类型。
	 *	'functionName' :回调是个函数
	 *	array('Common','load') :回调是静态类方法,如: Common::load()
	 *	array($obj,'load') :回调是对应的方法,如: $obj->load()
	 */
	static public function loadRegister($callback){
		spl_autoload_register($callback);
	}


	/**
	 * 任意多个变量的调试输出
	 *
	 * @param mixed [$var1,$var2,$var3,...]
	 */
	static public function dump()
	{
		echo '<pre style="font-size:12px; color:#0000FF">'.PHP_EOL;
		$vars = func_get_args();
		foreach ($vars as $var)
		{
			if (is_array($var)){
				print_r($var);
			}else if(is_object($var)){
				echo get_class($var)." Object";
			}else if(is_resource($var)){
				echo (string)$var;
			}else{
				echo var_dump($var);
			}
		}
	  	echo '</pre>'.PHP_EOL;
	}

	/**
	 * 与 dump() 方法一样,但会终于程序
	 *
	 * @param mixed [$var1,$var2,$var3,...]
	 */
	static public function dumpExit()
	{
		foreach (func_get_args() as $var){
			dump($var);
		}
		exit;
	}

	/**
	 * 框架调用：自动装入类库
	 *
	 * @param string $class
	 */
	static public function _autoload($class){
		// 查询框架中定义的类库或在搜索路径下查询
		$includeFile = self::_findClass($class);
		// 在找不到的情况下并且有定义第三方的装载器的话则把继续执行第三方的装载器
		if($includeFile=='' && count(spl_autoload_functions())>1){
			return true;
		}

		/**
		 * 如果是调试阶段则判断文件是否存在
		 *
		 * 这里这么做只是为了提示更友好而以，如果关闭的调试则不管了
		 */
		$appCfg = QP_Sys::getAppCfg();
		if($appCfg['debug'] && !file_exists($includeFile)){
			// (发现这里面抛异常不会被处理,所以只能模似了)
			$fileError = true;
			include QUICKPHP_PATH.'/Debug/Loaderror.php';
			die;
		}

		// 载入文件 (如果是调试模板下则载入了两次)
		require $includeFile;

		// 调试模式下再判断一下类是否定义正确了
		if($appCfg['debug'] && !class_exists($class)){
			$classError = true;
			include QUICKPHP_PATH.'/Debug/Loaderror.php';
			die;
		}
	}

	/**
	 * 框架调用：查找框架所能处理的类对应文件
	 *
	 * @param string $class
	 * @return string
	 */
	static private function _findClass($class){
		$class = strtolower($class);
		// 兼容控制器的继承(解决Linux下文件名大小写的问题)
		if(substr($class,-10) == 'controller'){
			$class = str_replace('controller','Controller',$class);
		}
		$dirArr = array_map('ucfirst',explode('_',$class));
		// 根据前缀来判断类的属性
		switch ($dirArr[0]){

			// 如果是框架类
			case 'Qp':
				$includeFile = QUICKPHP_PATH;
				foreach ($dirArr as $k=>$path){
					if($k > 0){
						$includeFile .= '/'.$path;
					}
				}
				$includeFile .= '.php';
				break;

			// 模型调用
			case 'Model':
				$includeFile = APPLICATION_PATH.'/Models';
				foreach ($dirArr as $k=>$path){
					if($k > 0){
						$includeFile .= '/'.$path;
					}
				}
				$includeFile .= '.php';
				break;

			// 其它自定义类库,则在 include_path 中搜索
			default:
				$baseFile = $spr = '';
				foreach ($dirArr as $path){
					$baseFile .= $spr.$path;
					$spr = '/';
				}
				$baseFile .= '.php';
				// 在搜索路径中查询
				$includeFile = '';
				$includePaths = explode(PATH_SEPARATOR, get_include_path());
				foreach ($includePaths as $path){
					$file = $path.'/'.$baseFile;
					if(file_exists($file)){
						$includeFile = $file;
						break;
					}
				}
				break;
		}
		return $includeFile;
	}

	/**
	 * 框架调用：异常统一处理,并且根据配置决定处理方式
	 *
	 * @param object $exception
	 */
	static public function _exception($exception){
		// APP的配置
		$appCfg = QP_Sys::getAppCfg();
		// 得到异常代码
		$code = $exception->getCode();
		// 永远抛出的异常
		$allowException = array(QP_Exception::EXCEPTION_NO_CONTROLLER, QP_Exception::EXCEPTION_NO_ACTION);
		// 如果设置为显示异常 或 为永远抛出的异常则调用 Error 控制器
		if(in_array($code,$allowException) || $appCfg['display_exception']){
			// 调用 ErrorController 并且执行它
			require APPLICATION_PATH.'/Controllers/ErrorController.php';
			$errorController = new ErrorController();
			$errorController->init();
			$errorController->errorAction($exception);
			// 是否自动解析视图
			if($errorController->viewIsAutoRender()){
				$errorController->view->setPath('Error');
				echo $errorController->view->render('Error'.QP_View::getDefaultExt());
			}
		}else{
			// 写日志
			$msg = 'Exception Message:'.$exception->getMessage().PHP_EOL;
			$msg .= 'Stack Trace:'.$exception->getTraceAsString().PHP_EOL;
			self::log($msg,'app');
		}
	}

	/**
	 * 根据设置选项进行PHP错误处理
	 */
	static public function _error($errno , $errstr, $errfile, $errline, $errcontext)
	{
		// APP的配置
		$appCfg = QP_Sys::getAppCfg();
		// 允许的错误类型将不进行日志记录
		$allowError = $appCfg['allow_error'];
		if(in_array($errno,$allowError)){
			return;
		}
		// 是显示出来还是写日志
		$display = $appCfg['display_error'];
		if($display){
			$msg = '<b>Error:</b>'.$errstr.'<br/>'.PHP_EOL;
			$msg .= '<b>File:['.$errline.']</b>'.$errfile.'<br/>'.PHP_EOL;
			if($errcontext){
				$msg .= '<b>Parsms:</b>'.print_r($errcontext,true).'<br/>'.PHP_EOL;
			}
			$msg .= '<br/>';
			echo($msg);
		}else{
			$msg = 'Error:'.$errstr.PHP_EOL;
			$msg .= 'File:['.$errline.']'.$errfile.PHP_EOL;
			self::log($msg,'php');
		}
	}


	/**
	 * 检测当前是否以 PHP CLI 方式运行,框架初始化时自动调用
	 */
	static public function _checkSapi()
	{
		// 不是则直接闪人
		if (PHP_SAPI != 'cli')
		{
			return;
		}

		// 根据框架的配置将 $_SERVER['argv'] 变量的值转成 REQUEST 的 GET 或 PARAM 值
		$request = QP_Request::getInstance();
		// APP的配置
		$appCfg = QP_Sys::getAppCfg();
		$argv = $request->server('argv');
		// 主程序文件名
		$prgName = $argv[0];
		unset($argv[0]);
		// URL模式为 standard 时的 GET KEY名映射
		$getKeyMap = array(
			'controller'=>QP_Request::C,
			'action'=>QP_Request::A,
		);
		// 设置所有参数
		foreach ($argv as $arg)
		{
			$arr = explode('=',$arg);
			if (count($arr) != 2)
			{
				echo <<<EOF
				syntax:
				php $prgName [controller=<controller> action=<action> param1=value1 param2=value2 ...]

				examples:
				php $prgName controller=index action=test id=10 name=vg

EOF;
				exit;
			}
			// 先设置控制器和动作
			list($key,$val) = $arr;
			if($appCfg['url_method'] == 'standard'){
				if(array_key_exists($key,$getKeyMap)){
					$key = $getKeyMap[$key];
				}
				$request->setGet($key,$val);
			}else{
				$request->setParam($key,$val);
			}
		}
	}

}

