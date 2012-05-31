<?php
/**
 * FILE 缓存类型
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: File.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入DB抽象类
 */
require_once QUICKPHP_PATH . '/Cache/Abstract.php';

class QP_Cache_File extends QP_Cache_Abstract
{
	/**
	 * 构造函数
	 *
	 * @param array $options 配置项,如果未指定缓存目录,则默认为 Application/Data/Cache
	 * @example $options = array('cache_path'=>'/tmp/')
	 */
	public function __construct($options=array())
	{
		// 设置默认的缓存目录
		if(! isset($options['cache_path'])){
			$options['cache_path'] = APPLICATION_PATH . "/Data/Cache/";
		}
		parent::__construct($options);
	}
	
	/**
	 * 设置缓存
	 *
	 * @param string $key 键名
	 * @param mixed $value 值
	 * @param int $expire 过期时间,单位秒
	 * @return boolean
	 */
	public function set($key, $value, $expire=0)
	{
		// 保存数据文件
		$this->_saveDataFile($key,$value);
		// 保存状态记录文件
		$this->_saveStatFile($key,$expire);
	}
	
	/**
	 * 获取缓存
	 *
	 * @param string $key 键名
	 * @return mixed
	 */
	public function get($key)
	{
		// 如果缓存不可用 或 已过期了
		if (!$this->_enable || !$this->_checkExpire($key))
		{
			return false;
		}
		return $this->_readDataFile($key);
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
		@unlink($this->_file($key,0));
		@unlink($this->_file($key,1));
		return true;
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
	 * 清空所有(自己手工清吧)
	 *
	 * @return boolean
	 */
	public function clear()
	{
		return true;
	}	
	
	/**
	 * 根据 KEY 生成文件名
	 * 
	 * @param string $key 键
	 * @param  int $type 文件类型 0:数据文件 1:状态文件
	 * @return string 绝对路径文件名
	 */
	private function _file($key,$type=0){
		$ext = $type==0 ? '.data' : '.stat';
		return $this->_options['cache_path'].$this->_key($key).$ext;
	}
	
	/**
	 * 保存数据文件
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	private function _saveDataFile($key,$value){
		$data = serialize($value);
		$file = $this->_file($key,0);
		file_put_contents($file,$data);
	}
	
	/**
	 * 读取数据文件
	 *
	 * @param string $key
	 * @return mixed
	 */
	private function _readDataFile($key){
		$value = @file_get_contents($this->_file($key,0));		
		if($value){
			$value = unserialize($value);
		}
		return $value;
	}
	
	/**
	 * 保存状态文件
	 * 
	 * 由于文件缓存没办法设置过期自动失效,所以只能生成一个对应的记录文件来保存以下数据了
	 * array('create'=>'创建时间','life'=>'过期时间');
	 *
	 * @param string $key
	 * @param int $expire
	 */
	private function _saveStatFile($key,$expire){
		$file = $this->_file($key,1);
		$stat = array('create'=>time(),'life'=>$expire);
		$data = '<?php return '.var_export($stat, true).' ?>';
		file_put_contents($file,$data);
	}
	
	/**
	 * 检查缓存 KEY 是否已过期，如果过期则删除它
	 *
	 * @param string $key
	 * @return boolean
	 */
	private function _checkExpire($key)
	{
		// 得到状态文件
		$file = $this->_file($key,1);
		
		// 如果文件不存在则认为缓存过期
		if(! file_exists($file)){
			return false;
		}
		
		// 得到状态
		$stat = include($file);
		
		// 缓存是否有效
		if (time() > ($stat['create'] + $stat['life']))
		{
			// 无效则删除
			@unlink($this->_file($key,0));
			@unlink($file);
			return false;
		}else{
			return true;
		}
	}		
}
