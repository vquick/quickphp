<?php
/**
 * 控制器基类
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Controller.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
abstract class QP_Controller
{
	/**
	 * 默认执行的控制器
	 *
	 */
	const DEFAULT_CONTROLLER = 'index';

	/**
	 * 默认执行的动作
	 *
	 */
	const DEFAULT_ACTION = 'index';

	/**
	 * 是否自动解析视图
	 *
	 * @var boolean
	 */
	protected $_viewAutoRender = true;

	/**
	 * 请求对象
	 *
	 * @var object
	 */
	protected $request;

	/**
	 * 视图对象
	 *
	 * @var object
	 */
	public $_view = null;

	/**
	 * 这样处理的机制是为了实现不至于任何情况下都装载 VIEW
	 *
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var)
	{
		if($var == 'view'){
			if(null === $this->_view){
				$this->_view = QP_View::getInstance();
				// 给视图注册这个全局请求对象,方便视图的操作
				$this->_view->request = $this->request;
			}
			return $this->_view;
		}
		return $this->$var;
	}

	/**
	 * 控制器初始化
	 *
	 */
	public function __construct()
	{
		$this->request = QP_Request::getInstance();
	}

	/**
	 * 设置是否自动解析视图
	 *
	 * @param boolean $bool
	 */
	public function setViewAutoRender($bool)
	{
		$this->_viewAutoRender = $bool;
	}

	/**
	 * 判断是否自动解析视图,这个方法一般是框架会调用
	 *
	 */
	public function viewIsAutoRender()
	{
		return $this->_viewAutoRender;
	}

	/**
	 * 自动运行,可以在自定义控制器中重载
	 *
	 */
	public function init(){}

	/**
	 * URL 直接跳转
	 *
	 * @param string $url URL地址
	 */
	public function location($url)
	{
		header("Location: $url");
		exit(0);
	}

	/**
	 * 跳转到指定控制器中的动作
	 *
	 * 建议：使用这个方法可以兼容各种URL模式 和 和解决路径问题
	 *
	 * @param string $controller 控制器
	 * @param string $action 动作
	 * @param array|string $params
	 */
	public function gotoUri($controller, $action=self::DEFAULT_ACTION, $params=null)
	{
		$url = QP_Sys::url($controller,$action,$params);
		$this->location($url);
	}

	/**
	 * 显示提示消息页
	 *
	 * 注意：子类可以重载这个方法以适合项目的使用
	 *
	 * @param string $msg :消息文本
	 * @param string $url :将要跳转的URL  "":自动返回到上一页  "close":则关闭窗口
	 * @param $time $time :页面显示停留的时间,单位:秒,过了时间后自动跳转
	 */
	public function msgbox($msg,$url='',$time=10)
	{
		QP_Sys::msgbox($msg,$url,$time);
	}
}
