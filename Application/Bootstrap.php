<?php
/**
 * APP 启动后自动运行，在这里可以做一些自定义操作，如自动登录,载入公用函数库等。
 *
 * @category QuickPHP
 * @copyright http://www.vquickphp.com
 * @version $Id: Bootstrap.php 905 2011-05-05 07:43:56Z yuanwei $
 */
class Bootstrap
{
	/**
	 * 框架只会自动调用该方法，用户自己的代码可加在里面.
	 *
	 */
	public function init(){
		// 使用 session
		// session_start();

		// 使用 Layout 开发
		QP_Layout::start();

		// 喜欢过程式编程则可以载入公用函数库, 把 Func.php 放到 Application\Library 目录下
		// 如果喜欢对象编程则可以参考 IndexController::otherAction() 中直接引用
		// require 'Func.php';

		// 路由配置示例，对应的URL: http://domain.com/user/1
		/*
		QP_Router::set('user', array(
			'uri'=>'user/(<id>)',
			'bind'=>array('id'=>'\d+'),
			'controller'=>'index',
			'action'=>'index',
		));
		*/
	}
}