<?php
/**
 * 框架视图
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: View.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入视图基类
 */
require QUICKPHP_PATH . '/View/Abstract.php';

/**
 * 使用 PHP 本身语法实现模板引擎
 * 继承基类中的方法，并且扩展很多实现方法
 *
 */
class QP_View extends QP_View_Abstract
{
	/**
	 * 单例模式
	 *
	 * @var object
	 */
	private static $_instance = null;
		
	/**
	 * 保存视图全局设置
	 * 
	 * @var array
	 */
	static private $_globalVars = array();

	/**
	 * 视图文件默认的扩展名
	 *
	 * @var unknown_type
	 */
	static private $_defaultExt = '.html';
	
	/**
	 * 设置视图文件默认的扩展名
	 *
	 * @param string $ext : 如 '.html'
	 */
	static public function setDefaultExt($ext='.html')
	{
		self::$_defaultExt = $ext;
	}
	
	/**
	 * 得到视图文件默认的扩展名
	 *
	 * @return string
	 */
	static public function getDefaultExt()
	{
		return self::$_defaultExt;
	}
	
	/**
	 * 设置视图全局变量
	 *
	 * @param string|array $key 键名或数据
	 * @param mixed $value 数据
	 */
	static public function setGlobal($key,$value=null)
	{
		if(is_array($key)){
			self::$_globalVars = array_merge(self::$_globalVars,$key);
		}else{
			self::$_globalVars[$key] = $value;
		}
	}
	
	/**
	 * 得到视图所有的全局变量
	 *
	 * @return array
	 */
	static public function getGlobal()
	{
		return self::$_globalVars;
	}
	
	/**
	 * 生成单个实例,这个方法框架会用到，APP中使用建议: new QP_View();
	 *
	 * @return Request object
	 */
	static public function getInstance()
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * 视图工厂
	 *
	 * @param string $viewBasePath 视图文件的根目录,为空时默认为:"Application/Views/Script/"
	 * @return view object
	 */	
	static public function factory($viewBasePath = null)
	{
		return new self($viewBasePath);
	}
	
	/**
	* 构造函数
	*
	* @param string $viewBasePath 视图文件的根目录,为空时默认为:"Application/Views/Script/"
	* @return void
	*/
	public function __construct($viewBasePath = null)
	{
		parent::__construct($viewBasePath);
	}
	
	/**
	 * 重载:返回解析后的视图
	 *
	 * @param string $name 视图文件名
	 * @return string 
	 */
	public function render($name)
	{
		$file = $this->getPath().$name;
		// 检测视图文件是否存在
		if(!file_exists($file)){
			throw new QP_Exception("视图不存在:$file");
		}
		// 是否有全局设置
		if(self::$_globalVars){
			$this->assign(self::$_globalVars);
		}
		// 如果开启了调试，则把所有输出到视图的数据保存起来用在 Debuginfo 中输出，方便开发者调试
		$appCfg = QP_Sys::getAppCfg();
		if($appCfg['debug']){
			$registry = QP_Registry_Registry::getInstance();
			$viewData = $registry->get('VIEW_DEBUG');
			$viewData[$name] = $this->_vars;
			$registry->set('VIEW_DEBUG', $viewData);
		}
		// 执行文件得到内容
		ob_start();
		include $file;
		return ob_get_clean();
	}
}
