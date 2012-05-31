<?php
/**
 * 框架统一入口
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Application.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/* 引入框架的核心文件,这里显示的引用是为了速度着想 */
require QUICKPHP_PATH.'/Sys.php';
require QUICKPHP_PATH.'/Controller.php';
require QUICKPHP_PATH.'/Dispatcher.php';
require QUICKPHP_PATH.'/Request.php';
require QUICKPHP_PATH.'/Router.php';
require QUICKPHP_PATH.'/Layout.php';
require QUICKPHP_PATH.'/View.php';
require QUICKPHP_PATH.'/Registry/Registry.php';

class QP_Application
{
	/**
	 * 构造函数
	 *
	 * @param string $appConfigFile APP的配置文件
	 */
	public function __construct($appConfigFile){
		// 载入APP配置
		QP_Registry_Registry::getInstance()->set('APP_CFG',include($appConfigFile));
	}

	/**
	 * 开始初始化框架并且运行APP
	 *
	 */
	public function run(){
		// 加入自动搜索路径
		set_include_path(implode(PATH_SEPARATOR, array(
		    realpath(APPLICATION_PATH . '/Library'),
		    realpath(APPLICATION_PATH . '/Controllers'),
		    get_include_path(),
		)));
		// 系统初始化
		$this->_initSys();
		// APP初始化
		$this->_initApp();
		// 执行调配器
		$this->_dispatch();
	}

	/**
	 * 系统初始化
	 *
	 */
	private function _initSys(){
		// 错误最高级别
		error_reporting(E_ALL);
		// 自定义PHP错处处理
		set_error_handler(array('QP_Sys', '_error'));
		// 初始化自动装载器
		spl_autoload_register(array('QP_Sys', '_autoload'));
		// 异常处理
		set_exception_handler(array('QP_Sys', '_exception'));
		// 检测框架是否在 CLI 模式下运行 (PHP命令行模式)
		QP_Sys::_checkSapi();
	}

	/**
	 * 应用程序初始化
	 *
	 */
	private function _initApp(){
		// 得到APP配置
		$appCfg = QP_Sys::getAppCfg();
		// 设置时区
		date_default_timezone_set($appCfg['timezone']);
	}

	/**
	 * 执行调配器
	 *
	 */
	private function _dispatch(){
		$dispatcher = new QP_Dispatcher();
		$dispatcher->run();
	}
}
