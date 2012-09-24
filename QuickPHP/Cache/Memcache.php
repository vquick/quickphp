<?php
/**
 * MEMCACHE 缓存类型
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Memcache.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入DB抽象类
 */
require_once QUICKPHP_PATH . '/Cache/Abstract.php';

class QP_Cache_Memcache extends QP_Cache_Abstract
{
	/**
	 * MEMCACHE 对象
	 *
	 * @var object
	 */
	private $_memcache = null;
	
	/**
	 * Memcache 配置
	 *
	 * @var array
	 */
	private $_server = array
	(
		// 主机
		'host'=>'127.0.0.1',
		// 端口
		'port'=>11211,
		// 是否持久连接
		'persistent'=>true,
		// 服务器的权重
		'weight'=>1,
		// 连接的持续时间(秒)
		'timeout'=>1,
		// 连接的重试时间, -1:不重试
		'retry_interval' => 15,
		// 服务器的在线状态
		'status'=>true,
		// 错误处理的回调函数
		'failure_callback'=>null,
	);
	
	/**
	 * 构造函数
	 *
	 * @param array $server 服务器配置
	 */
	public function __construct($server=array())
	{
		// 是否安装了扩展
		if(! extension_loaded('memcache')){
			throw new QP_Exception('Memcache 扩展没有安装.');
		}
		// 创建对象
		$this->_memcache = new Memcache;
		// 连接服务器
		$this->_server = array_merge($this->_server,$server);
		$this->addServer($this->_server);	
	}
	
	/**
	 * 添加 MEMCACHE 主机
	 *
	 * @param array $server 
	 */
	public function addServer(array $server)
	{
		$this->_memcache->addServer(
			$server['host'], 
			$server['port'], 
			$server['persistent'],
			$server['weight'], 
			$server['timeout'],
			$server['retry_interval'],
			$server['status'], 
			$server['failure_callback']
		);		
	}

	/**
	 * 得到 memcache 原始对象
	 *
	 * @return unknown
	 */
	public function getMemcache()
	{
		return $this->_memcache;
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
		return $this->_memcache->set($this->_key($key) , $value, MEMCACHE_COMPRESSED, $expire);
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
		return $this->_memcache->get($this->_key($key), MEMCACHE_COMPRESSED);
	}

	/**
	 * 批量得到缓存值
	 *
	 * @param array $key 键数组,如 array('key1','key2')
	 * @return unknown
	 */
	public function gets($key=array())
	{
		// 如果缓存不可用
		if (! $this->_enable)
		{
			return false;
		}
		// 处理 KEY
		$keyArr = array();
		foreach ($key as $k){
			$keyArr[] = $this->_key($k);
		}
		$keyArr = array_map(array($this,'_key'), $key);
		return $this->_memcache->get($keyArr, MEMCACHE_COMPRESSED);
	}

	/**
	 * 删除缓存
	 *
	 * @param string $key 键
	 * @return boolean
	 */
	public function remove($key)
	{
		return $this->_memcache->delete($this->_key($key));
	}

	/**
	 * 刷新
	 *
	 * @return boolean
	 */
	public function flush()
	{
		return $this->_memcache->flush();
	}

	/**
	 * 清空所有
	 *
	 * @return boolean
	 */
	public function clear()
	{
		return true;
	}
	
	/**
	 * 重载该方法，因为为影响 gets() 方法
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _key($key)
	{
		return $key;
	}
}
