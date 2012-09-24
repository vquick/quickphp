<?php
/**
 * VIEW 接口,用于扩展模板解析引擎
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Interface.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
interface QP_View_Interface
{
	/**
	 * 返回模板引擎对象
	 *
	 * @return object
	 */
	public function getEngine();
	
	/**
	 * 设置模板文件目录
	 *
	 * @return void
	 */
	public function setPath($path);
	
	/**
	 * 得到模板文件目录
	 *
	 * @return array
	 */
	public function getPath();
	
	/**
	 * 设置变量到视图
	 *
	 * @param string $key 键名
	 * @param mixed $val 数据
	 * @return void
	 */
	public function __set($key, $val);
	
	
	/**
	 * 得到视图变量
	 *
	 * @param string|array $key 键名或数据
	 * @param mixed $val 数据
	 * @return void
	 */
	public function __get($key);
	
	/**
	 * 判断视图变量是否定义了
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key);
	
	/**
	 * 注销视图变量
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key);
	
	/**
	 * 设置变量的值
	 * 
	 * @see __set()
	 * @param string|array $key 键名或数据
	 * @param mixed $value 数据
	 * @return void
	 */
	public function assign($spec, $value = null);
	
	
	/**
	 * 返回解析后的视图
	 *
	 * @param string $name 视图文件名
	 * @return string 
	 */
	public function render($name);
}
