<?php
/**
 * 视图助手基类,所有助手必需都继承它
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Helper.php 1236 2012-1-17 08:52:02Z yuanwei $
 */

Abstract class QP_View_Helper
{
	/**
	 * 请求对象
	 *
	 * @var object
	 */
	protected $request;

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct(){
		$this->request = QP_Request::getInstance();
	}
	
	/**
	 * 自动初始化过程,助手子类可以重载这个方法
	 *
	 * @return void
	 */
	public function init(){
	}
}
