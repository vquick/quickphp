<?php
/**
 * 系统默认控制器
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: IndexController.php 905 2011-05-05 07:43:56Z yuanwei $
 */
class IndexController extends QP_Controller
{
	/**
	 * 自动运行
	 */
	public function init(){
	}

	/**
	 * 默认动作
	 */
	public function indexAction(){
		$this->view->content = '*QuickPHP真的不错哦*';
	}

	/**
	 * 其它动作
	 */
	public function otherAction(){
		// 不使用Layout, Application/Bootstrap.php 中自动打开了它。
		QP_Layout::stop();

		// 不自动解析视图,框架在默认情况下是会自动解析视图的
		$this->setViewAutoRender(false);

		// 引用自定义类
		$myclass = new Myclass();
		$myclass->test();

		// 引用自定义模型,并且使用DB功能，注意一定要先配置 Application/Configs/Database.php
		$mymodel = new Model_Mymodel();
		$mymodel->db();
	}
}