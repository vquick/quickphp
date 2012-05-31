<?php
/**
 * SESSION 相关操作
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Session.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Session_Session
{
	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	* 设置SESSION的值
	*
	* @param string $name :SESSION变量名称
	* @param mixed $value :对应值
	*/
	static public function set($name,$value='')
	{
		if(!is_array($name))
		{
			$_SESSION[$name] = $value;
		}else{
			foreach ($name as $k=>$v) $_SESSION[$k] = $v;
		}
	}

	/**
	* 获得 SESSION 数据
	*
	* @param string $name:域名称,如果为空则返回整个 $SESSION 数组
	* @return mixed
	*/
	static public function get($name='')
	{
		return  $name ? (isset($_SESSION[$name]) ? $_SESSION[$name] : null) : $_SESSION;
	}

	/**
	* 删除指定的 SESSION
	*/
	static public function remove($name)
	{
		if(is_array($name)){
			foreach ($name as $n){
				if(isset($_SESSION[$n])){
					unset($_SESSION[$n]);
				}
			}
		}else{
			if(isset($_SESSION[$name])){
				unset($_SESSION[$name]);
			}
		}
		return true;
	}

	/**
	* 清空 SESSION
	*/
	static public function clear()
	{
		session_destroy();
	}
}
