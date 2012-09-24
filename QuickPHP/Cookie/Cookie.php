<?php
/**
 * COOKIE 相关操作
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Cookie.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Cookie_Cookie
{
	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	* 获得 COOKIE 数据
	*
	* @param string $name:域名称,如果为空则返回整个 $COOKIE 数组
	* @param boolean $decode:是否自动解密,如果 set() 时加密了则这里必需要解密,并且解密只能针对单个值
	* @return mixed
	*/
	static public function get($name='',$decode=false)
	{
		$value =  $name ? (isset($_COOKIE[$name]) ? $_COOKIE[$name] : null) : $_COOKIE;
		return ($name && $decode) ? self::_decode($value) : $value;
	}

	/**
	* 设置COOKIE
	*
	* @param string $name :COOKIE名称
	* @param string $value :值
	* @param int $time :有效时间,以秒为单位  0:表示会话期间内
	* @param string $domain :域名
	* @param boolean $encode :是否加密
	*/
	static public function set($name, $value='', $time=0, $domain=null, $encode=false)
	{
		$time = ($time == 0) ? 0 : (time()+$time);
		$value = $encode ? self::_encode($value) : $value;
		return setcookie($name, $value, $time, '/', $domain);
	}

	/**
	* 删除 COOKIE
	*
	* @param string $name :COOKIE名称
	*/
	static public function remove($name)
	{
		self::set($name,'',-86400 * 365);
	}


	/**
	* 清除 COOKIE
	*/
	static public function clear()
	{
		foreach ($_COOKIE as $key=>$val){
			self::remove($key);
		}
	}

	/**
	* 私有方法：加密 COOKIE 数据
	*/
	static private function _encode($str)
	{
		$str = base64_encode($str);
		$search = array('=','+','/');
		$replace = array('_','-','|');
		return str_replace($search,$replace,$str);
	}

	/**
	* 私有方法：解密 COOKIE 数据
	*/
	static private function _decode($str)
	{
		$replace = array('=','+','/');
		$search = array('_','-','|');
		$str = str_replace($search,$replace,$str);
		return base64_decode($str);
	}
}
