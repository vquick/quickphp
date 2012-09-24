<?php
/**
 * VIEW 抽象类,子类必需都继承它
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Abstract.php 1236 2012-1-17 08:52:02Z yuanwei $
 */

/**
 * 引入视图接口
 */
require_once QUICKPHP_PATH . '/View/Interface.php';

Abstract class QP_View_Abstract implements QP_View_Interface
{
	/**
	* 模板路径
	*
	* @var string
	*/
	protected $_path = null;

	/**
	* 模板文件名
	*
	* @var string
	*/
	protected $_file = null;
	
	/**
	 * 模板变量
	 *
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * 模板根目录
	 *
	 * @var string
	 */
	private $_basePath = '';
	
	/**
	 * 视图助手对象
	 *
	 * @var array-object
	 */
	static private $_helper = array();

	/**
	 * 构造函数
	 *
	 * @param string $viewBasePath 视图文件的根目录,为空时默认为:"Application/Views/Scripts" 表示相对路径
	 * @return void
	 */
	public function __construct($viewBasePath = null)
	{
		if($viewBasePath){
			$this->_basePath = $viewBasePath;
		}else{
			$this->_basePath = APPLICATION_PATH.'/Views/Scripts';
		}
	}

	/**
	* 实现接口,子类可重载
	* 返回板式模引擎对象
	*
	* @return object
	*/
	public function getEngine()
	{
		return $this;
	}

	/**
	* 实现接口,子类可重载
	*
	* 设置模板文件目录
	*
	* 注意:这里设置的的路径只要以视图的根目录开始，例如:
	* 假设视图完整的路径为:"/vhost/site/Application/Views/Script/Views/Script/Index/"
	* 则只要: setPath('Index')
	*
	* @param string $path 目录
	* @param boolean $force 目录属性 true:[$path为绝对路径] false:[$path为相对路径(相对于： Application/Views/Script/Views/Script )]
	* @return void
	*/
	public function setPath($path, $force=false)
	{
		// 相对路径时自动以 '/' 开始
		if(! $force){
			if(substr($path,0,1) != '/'){
				$path = '/'.$path;
			}
		}
		// 不管怎样都自动以"/" 结束
		if(substr($path,-1,1) != '/'){
			$path .= '/';
		}
		$this->_path = $force ? $path : $this->_basePath.$path;
	}

	/**
	* 实现接口,子类可重载
	* 得到脚本路径
	*
	* @return string
	*/
	public function getPath()
	{
		return $this->_path ? $this->_path : $this->_basePath;
	}

	/**
	* 实现接口,子类可重载:设置变量到视图
	*
	* @param string $key 键名
	* @param mixed $val 数据
	* @return void
	*/
	public function __set($key, $val)
	{
		$this->_vars[$key] = $val;
	}

	/**
	* 实现接口
	* 得到视图变量
	*
	* @param string|array $key 键名或数据
	* @param mixed $val 数据
	* @return void
	*/
	public function __get($key)
	{
		return isset($this->_vars[$key]) ? $this->_vars[$key] : null;
	}

	/**
	* 实现接口
	* 判断视图变量是否定义了
	*
	* @param string $key
	* @return boolean
	*/
	public function __isset($key)
	{
		return isset($this->_vars[$key]);
	}

	/**
	* 实现接口
	* 注销视图变量
	*
	* @param string $key
	* @return void
	*/
	public function __unset($key)
	{
		if(isset($this->_vars[$key])){
			unset($this->_vars[$key]);
		}
	}

	/**
	* 实现接口,子类可重载
	* 设置变量的值
	*
	* @see __set()
	* @param string|array $key 键名或数据
	* @param mixed $value 数据
	* @return void
	*/
	public function assign($key, $value = null)
	{
		if(is_array($key)){
			$this->_vars = array_merge($this->_vars, $key);
		}else{
			$this->_vars[$key] = $value;
		}
		return $this;
	}


	/**
	* 实现接口,子类要重载实现它
	* 返回解析后的视图
	*
	* @param string $name 视图文件名
	* @return string
	*/
	public function render($name){}

	/**
	* 视图助手,助手都定义在 Application/Views/Helpers
	*
	* @param string $name 助手名，默认情况下为当前控制器所对应的
	* @return object
	*/
	public function helper($name='')
	{
		// 默认为当前控制器所对应的助手
		if($name == ''){
			// 根据不同的URL模式得到当前的控制器
			$request = QP_Request::getInstance();
			$appConfig = QP_Sys::getAppCfg();
			$param = $appConfig['url_method']=='standard' ? $request->getGet() : $request->getParam();
			$name = $param['controller'];
		}
		// 得到助手文件名
		$name = ucfirst(strtolower($name));
		
		// 如果是新的助手对象已生成则要生成它
		if(!isset(self::$_helper[$name]) || !is_object(self::$_helper[$name])){
			// 助手文件不存在
			$helperFile = APPLICATION_PATH.'/Views/Helpers/'.$name.'.php';
			if(! file_exists($helperFile)){
				throw new QP_Exception("助手文件不存在:$helperFile",QP_Exception::EXCEPTION_NO_HELPER);
			}
			
			// 包含助手生成对象
			require_once($helperFile);
			$className = 'Helper_'.$name;
			
			// 类是否存在的
			if(! class_exists($className,false)){
				throw new QP_Exception("类:$className 未定义在:$helperFile");
			}
			
			// 判断助手是否继承基类
			self::$_helper[$name] = new $className();
			if(! (self::$_helper[$name] instanceof QP_View_Helper)){
				throw new QP_Exception("助手类 $className 必需继承 QP_View_Helper 基类");
			}
			$obj = self::$_helper[$name];
			self::$_helper[$name]->init();
		}
		return self::$_helper[$name];
	}

}
