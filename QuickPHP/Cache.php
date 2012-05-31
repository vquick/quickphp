<?php
/**
 * 缓存工厂
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Cache.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Cache
{

	/**
	 * 单例模式
	 *
	 * @var object
	 */
	private static $_instance = array();

	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	 * 得到缓存实例对象
	 *
	 * @param string $type 缓存类型,可选项有: 'memcache' | 'apc' | 'file'
	 * @param $options $options 配置项，具体请看 QuickPHP/Cache 目录下各缓存类型的构造函数
	 * @return object
	 */
	static public function factory($type='file', $options=array())
	{
		// 判断驱动是否已经生成对应的对象了
		$type = ucfirst(strtolower($type));
		if(! isset(self::$_instance[$type])){
			// 判断类型文件是否存在
			$file = QUICKPHP_PATH.'/Cache/'.$type.'.php';
			if(! file_exists($file)){
				throw new QP_Exception('缓存类型文件不存在:'.$file);
			}
			// 生成对象
			require $file;
			$class = 'QP_Cache_'.$type;
			self::$_instance[$type] = new $class($options);
		}
		return self::$_instance[$type];
	}

}
