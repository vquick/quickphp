<?php
/**
 * 框架调配器
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Dispatcher.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

class QP_Dispatcher
{
	/**
	 * 请求组件对象
	 *
	 * @var object
	 */
	private $_request;

	/**
	 * 当前控制器名
	 *
	 * @var string
	 */
	private $_controller;

	/**
	 * 制器的相对路径
	 *
	 * @var string
	 */
	private $_controllerPath;

	/**
	 * 当前动作名
	 *
	 * @var string
	 */
	private $_action;

	/**
	 * APP的配置
	 *
	 * @var array
	 */
	private $_appConfig = array();

	/**
	 * 保存APP运行时的信息
	 *
	 * @var array
	 */
	private $_debugInfo = array();

	/**
	 * 构造函数
	 *
	 */
	public function __construct()
	{
		$this->_request = QP_Request::getInstance();
		$this->_appConfig = QP_Sys::getAppCfg();
	}

	/**
	 * 运行调度器
	 *
	 */
	public function run()
	{
		// APP运行的开始时间
		$this->_setDebugInfo('beginTime',microtime(true));

		// 运行应用的 Bootstrap
		require APPLICATION_PATH.'/Bootstrap.php';
		$bootStrap = new Bootstrap();
		$bootStrap->init();

		// 解析当前的URI(包括解析路由)
		$this->_parseUri();

		// 得到控制器
		$controller = $this->_getController();
		$action = $this->_action.'Action';

		// 判断动作是否存在
		if(! method_exists($controller,$action)){
			throw new QP_Exception('控制器:'.get_class($controller).' 中未定义动作:'.$action, QP_Exception::EXCEPTION_NO_ACTION);
		}

		// 选择的动作
		$this->_setDebugInfo('action',$action);

		// 把执行的输出先保存起来放到 Layout 中显示
		ob_start();

		// 执行 init 方法
		$controller->init();

		// 执行动作
		$controller->$action();

		// 得到所有输出内容
		$viewContent = ob_get_clean();

		// 是否自动输出视图
		if($controller->viewIsAutoRender()){
			$controller->view->setPath($this->_controllerPath);
			$viewContent .= $controller->view->render($this->_action.QP_View::getDefaultExt());

			// 使用的视图
			$this->_setDebugInfo('view',$this->_action.QP_View::getDefaultExt());
		}

		// 是否启用了 Layout
		if(QP_Layout::isEnabled()){
			// 将 Layout 当作当前视图的一部分
			$view = QP_View::getInstance();
			$view->setPath(APPLICATION_PATH.'/Views/Layouts', true);

			// 将布局的变量与重新赋值给视图
			$view->assign(QP_Layout::get());

			// 'LayoutContent' 为框架的布局内容引用
			$view->LayoutContent = $viewContent;
			$layoutName = QP_Layout::name();
			$viewContent = $view->render($layoutName);

			// 使用的 Layout
			$this->_setDebugInfo('layout',$layoutName);
		}

		// 如果是 SAPI 运行模式就要设置字符集,防止乱码
		if (PHP_SAPI != 'cli'){
			header("Content-Type:text/html; charset=".$this->_appConfig['charset']);
		}

		// 有视图内容则输出
		if($viewContent != ''){
			echo $viewContent;
		}

		// APP运行的结束时间
		$this->_setDebugInfo('endTime',microtime(true));

		// 如果显示调试信息 并且 当前不是 AJAX 的请求 并且框架不是以 CLI方式运行
		if($this->_appConfig['debug'] && !$this->_request->isAjax() && PHP_SAPI != 'cli'){
			$debugInfo = $this->_debugInfo;
			include QUICKPHP_PATH.'/Debug/Debuginfo.php';
		}
	}


	/**
	 * 解析 URI 得到对应的当前控制器和动作
	 */
	private function _parseUri()
	{
		// 根据不同的 URL 进行解析
		if($this->_appConfig['url_method'] == 'standard'){
			// 得到控制器名和动作名
			$this->_controller = ucfirst(strtolower($this->_request->getGet(QP_Request::C, QP_Controller::DEFAULT_CONTROLLER)));
			$this->_action = ucfirst(strtolower($this->_request->getGet(QP_Request::A, QP_Controller::DEFAULT_ACTION)));
			// 将控制器与方法加入到 GET 中
			$this->_request->setGet(array(
				'controller'=>$this->_controller,
				'action'=>$this->_action
			));
		}else{
			$isRouter = false;
			// URL重写等就要检查是否设置了路由
			if(QP_Router::isUseRouter()){
				$router = QP_Router::matches($this->_request->requestUri());
				// 路由匹配成功
				if($router){
					$this->_controller = $router['controller'];
					$this->_action = $router['action'];
					$isRouter = true;
					// 匹配到的路由
					$this->_setDebugInfo('router',$router);
				}
			}
			// 如果没有定义或匹配到路由则 继续 采用 常规方法 得到 控制器名 和 动作名
			if(! $isRouter){
				$this->_controller = ucfirst(strtolower($this->_request->getParam('controller', QP_Controller::DEFAULT_CONTROLLER)));
				$this->_action = ucfirst(strtolower($this->_request->getParam('action', QP_Controller::DEFAULT_ACTION)));
			}
			// 将控制器与方法加入到参数中
			$this->_request->setParam(array(
				'controller'=>$this->_controller,
				'action'=>$this->_action
			));
		}
	}

	/**
	 * 得到当前的控件器
	 */
	private function _getController()
	{
		// 处理 控制器名是否含有目录的情况,如 "admin_admin"
		if(false !== strpos($this->_controller, '_')){
			$arr = array_map('strtolower',explode('_',$this->_controller));
			$this->_controllerPath = implode('/',array_map('ucfirst',$arr));
		}else{
			$this->_controllerPath = $this->_controller;
		}
		// 得到控制器文件名
		$controllerFile = APPLICATION_PATH.'/Controllers/'.$this->_controllerPath.'Controller.php';
		if(! file_exists($controllerFile)){
			throw new QP_Exception("控制器不存在:$controllerFile",QP_Exception::EXCEPTION_NO_CONTROLLER);
		}

		// 包含控制器生成对象
		require ($controllerFile);
		$className = $this->_controller.'Controller';

		// 类是否存在的
		if(! class_exists($className,false)){
			throw new QP_Exception("类:$className 未定义在:$controllerFile");
		}

		// 判断控制器是否继承基类
		$controller = new $className();
		if(! ($controller instanceof QP_Controller)){
			throw new QP_Exception("控制器类 $className 必需继承 QP_Controller 基类");
		}

		// 当前使用的控制器
		$this->_setDebugInfo('controller',$className);
		return $controller;
	}

	/**
	 * 设置APP调试信息
	 *
	 * @param string $key 键
	 * @param string $value 内容
	 */
	private function _setDebugInfo($key,$value){
		if($this->_appConfig['debug']){
			$this->_debugInfo[$key] = $value;
		}
	}
}
