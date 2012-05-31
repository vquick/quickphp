<?php
/**
 * 缓存接口,用于扩展数据库驱动,所有的缓存类型必需实现这些接口
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Interface.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
interface QP_Cache_Interface
{
	// 设置
	public function set($key, $value, $expire=0);
	
	// 获得单个值
	public function get($key);
	
	// 同时获得多个值
	public function gets($keys=array());
	
	// 删除
	public function remove($key);
	
	// 刷新
	public function flush();
	
	// 清空所有
	public function clear();
}
