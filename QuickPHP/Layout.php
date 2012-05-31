<?php
/**
 * 框架布局
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Layout.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Layout{

	/**
	 * 默认布局名
	 *
	 */
	const DEFAULT_NAME = 'Main.html';

	/**
	 * 是否使用 Layout
	 *
	 * @var boolean
	 */
	static private $_enabled = false;

	/**
	 * 布局名称
	 *
	 * @var string
	 */
	static private $_name = '';

	/**
	 * 布局模板变量,控制器动作对应的视图内容变量是: "LayoutContent"
	 *
	 * @var array
	 */
	static private $_vars = array();	
	
	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	 * 开始使用布局
	 *
	 * 注意：
	 * 布局文件必需定义在 Application/Views/Layouts 目录下
	 *
	 * @param string $layoutName 布局文件名
	 */
	static public function start($layoutName = self::DEFAULT_NAME){
		self::$_name = $layoutName;
		self::$_enabled = true;
	}
	
	/**
	 * 设置布局模块变量的值
	 *
	 * @param string|array $key 键名或数据
	 * @param mixed $value 数据
	 * @return void
	 */
	static public function set($key, $value = null){
		if(is_array($key)){
			self::$_vars = array_merge(self::$_vars, $key);
		}else{
			self::$_vars[$key] = $value;
		}
	}
	
	/**
	 * 得到布局模块变量的值
	 *
	 * @param string $key
	 * @return mixed
	 */
	static public function get($key=''){
		return $key=='' ? self::$_vars : (isset(self::$_vars[$key]) ? self::$_vars[$key] : '');
	}	

	/**
	 * 禁用布局
	 *
	 */
	static public function stop(){
		self::$_enabled = false;
	}

	/**
	 * 布局是否有效
	 *
	 * @return boolean
	 */
	static public function isEnabled(){
		return self::$_enabled;
	}

	/**
	 * 得到当前布局名
	 *
	 * @param $name 如果设置值则 Layout 登录这个值，否则返回当前的 Layout 名称
	 * @return string
	 */
	static public function name($name=''){
		if($name){
			self::$_name = $name;
		}
		return self::$_name;
	}

}
