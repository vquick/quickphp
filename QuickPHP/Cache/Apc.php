<?php
/**
 * APC 缓存类型
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Apc.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入DB抽象类
 */
require_once QUICKPHP_PATH . '/Cache/Abstract.php';

class QP_Cache_Apc extends QP_Cache_Abstract
{
	/**
	 * 构造函数
	 */
	public function __construct(){
		// 是否安装了扩展
		if(! extension_loaded('apc')){
			throw new QP_Exception('Apc 扩展没有安装.');
		}
	}

	/**
	 * 设置缓存
	 *
	 * @param string $key 键名
	 * @param mixed $value 值
	 * @param int $expire 过期时间,单位秒 0:永远有效
	 * @return boolean
	 */
	public function set($key, $value, $expire=0)
	{
		return apc_store($this->_key($key),$value,$expire);
	}


	/**
	 * 获取缓存
	 *
	 * @param string $key 键名
	 * @return mixed
	 */
	public function get($key)
	{
		// 如果缓存不可用
		if (! $this->_enable)
		{
			return false;
		}
		return apc_fetch($this->_key($key));
	}

	/**
	 * 批量得到缓存值
	 *
	 * @param array $key 键数组,如 array('key1','key2')
	 * @return unknown
	 */
	public function gets($key=array())
	{
		$result = array();
		foreach ($key as $k)
		{
			$result[$k] = $this->get($k);
		}
		return $result;
	}

	/**
	 * 删除缓存
	 *
	 * @param string $key 键
	 * @return boolean
	 */
	public function remove($key)
	{
		return apc_delete($this->_key($key));
	}

	/**
	 * 刷新
	 *
	 * @return boolean
	 */
	public function flush()
	{
		return true;
	}

	/**
	 * 清空所有
	 *
	 * @return boolean
	 */
	public function clear()
	{
		return apc_clear_cache();
	}
}

