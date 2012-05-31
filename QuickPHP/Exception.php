<?php
/*
 * 框架异常基类
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Exception.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Exception extends Exception
{
	/**
	 * 异常常量定义
	 *
	 */
	// 控制器找不到
	const EXCEPTION_NO_CONTROLLER = 10000;
	// 动作找不到
	const EXCEPTION_NO_ACTION = 10001;
	// 助手找不到
	const EXCEPTION_NO_HELPER = 10002;
	// DB异常和查询错误
	const EXCEPTION_DB_ERROR = 10003;
	
	/**
	* @var null|Exception
	*/
	private $_previous = null;
	
	/**
	* 构造函数
	*
	* @param  string $msg
	* @param  int $code
	* @param  Exception $previous
	* @return void
	*/
	public function __construct($msg = '', $code = 0, Exception $previous = null)
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			parent::__construct($msg, (int) $code);
			$this->_previous = $previous;
		} else {
			parent::__construct($msg, (int) $code, $previous);
		}
	}
	
	/**
	* Overloading
	*
	* For PHP < 5.3.0, provides access to the getPrevious() method.
	*
	* @param  string $method
	* @param  array $args
	* @return mixed
	*/
	public function __call($method, array $args)
	{
		if ('getprevious' == strtolower($method)) {
			return $this->_getPrevious();
		}
		return null;
	}
	
	/**
	* 重载方法
	*
	* @return string
	*/
	public function __toString()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			if (null !== ($e = $this->getPrevious())) {
				return $e->__toString()
					. "\n\nNext "
					. parent::__toString();
			}
		}
		return parent::__toString();
	}
	
	/**
	* Returns previous Exception
	*
	* @return Exception|null
	*/
	protected function _getPrevious()
	{
		return $this->_previous;
	}
}
