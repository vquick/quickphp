<?php
/**
 * 对象仓库组件
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Registry.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Registry_Registry
{
	/**
	 * 单例对象
	 *
	 * @var object
	 */
	static private $_instance = null;

	/**
	 * 数据连接池
	 *
	 * @var object ArrayObject
	 */
	static private $_data = null;

	/**
	 * 框架函数，但不能被实例化
	 *
	 */
	private function __construct(){
		if(null === self::$_data){
			self::$_data = new ArrayObject();
		}
	}

	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __clone(){}

	/**
	 * 得到对象单例
	 *
	 */
	static public function getInstance(){
		if(null === self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * 设置数据
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key,$value='')
	{
		if (is_array($key)){
			foreach ($key as $k=>$v){
				self::$_data->offsetSet($k,$v);
			}
		}else{
			self::$_data->offsetSet($key,$value);
		}
	}

	/**
	 * 获得数据
	 *
	 * @param string $key :键名
	 * @return mixed
	 */
	public function get($key, $default=null)
	{
		return $this->keyExists($key) ? self::$_data->offsetGet($key) : $default;
	}

	/**
	 * 判断键是否存在的
	 *
	 * @param string $key
	 */
	public function keyExists($key){
		return self::$_data->offsetExists($key);
	}

	/**
	 * 删除键对应的值
	 *
	 * @param string $key
	 */
	public function remove($key){
		if($this->keyExists($key)){
			self::$_data->offsetUnset($key);
			return true;
		}else{
			return false;
		}
	}

}
