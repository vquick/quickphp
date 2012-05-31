<?php
/**
 * Cache 抽象类,子类必需都继承它
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Abstract.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入接口
 */
require_once QUICKPHP_PATH . '/Cache/Interface.php';

abstract class QP_Cache_Abstract implements QP_Cache_Interface
{
	/**
	 * 配置属性
	 *
	 * @var array
	 */
	protected $_options = array(
		// 缓存文件存放目录,如果是文件类型时则会自动设置该属性，开发者也可以自行设置
		'cache_path'=>'',
		
		// Memcache 属性,详细请看
		'memcache'=>array(
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
		),
	);
	
	/**
	 * 缓存是否可用
	 *
	 * @var boolean
	 */
	protected $_enable = true;
	
	/**
	 * 构造函数
	 *
	 * @param array $options
	 */
	public function __construct($options=array()){
		if(isset($options['memcache'])){
			$options['memcache'] = array_merge($this->_options['memcache'], $options['memcache']);
		}
		$this->_options = array_merge($this->_options, $options);
	}
	
	/**
	 * 设置缓存是否可用
	 *
	 * @param boolean $bool
	 */
	public function enable($bool=true)
	{
		$this->_enable = $bool;
	}

	/**
	 * 处理 KEY
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _key($key)
	{
		return md5($key);
	}
}
