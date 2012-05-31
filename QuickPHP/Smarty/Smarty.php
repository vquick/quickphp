<?php
/**
 * SMARTY 视图引擎
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Smarty.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入视图基类
 */
require_once QUICKPHP_PATH . '/View/Abstract.php';

class QP_Smarty_Smarty extends QP_View_Abstract
{
	/**
	 * SMARTY 标识左开始
	 *
	 */
	const LEFT_DELIMITER = '<?';

	/**
	 * SMARTY 标识右结束
	 *
	 */
	const RIGHT_DELIMITER = '?>';

	/**
	 * SMARTY 对象
	 *
	 * @var object
	 */
	private $_smarty = null;

	/**
	 * 构造函数
	 *
	 * @param string $viewBasePath 视图文件的根目录,为空时默认为:"Application/Views/Script/"
	 */
	public function __construct($viewBasePath = null)
	{
		// 初始化模板根目录
		parent::__construct($viewBasePath);

		// APP配置
		$appCfg = QP_Sys::getAppCfg();

		// 是否安装了 SMARTY 库文件
		$smartyFile = QUICKPHP_PATH.'/Smarty/Smarty/Smarty.class.php';
		if(! file_exists($smartyFile)){
			throw new QP_Exception('<hr/>
			请到 <a href="http://http://www.smarty.net/download" target="_blank">Smarty 官网</a>
			或到 <a href="http://www.vquickphp.com" target="_blank">QuickPHP 官网</a>
			下载 Smarty 放到 QuickPHP/Smarty/Smarty 目录下 <br/>
			注意:<font color="red">只支持 Smarty 2 Releases 的版本</font>(足够用啦，Smarty 3.x 没研究过它^_^)
			<hr/>');
		}

		// 创建 SMARTY 对象
		require_once $smartyFile;
		$this->_smarty = new Smarty();

		// 左/右 标记定义
		$this->_smarty->left_delimiter = self::LEFT_DELIMITER;
		$this->_smarty->right_delimiter = self::RIGHT_DELIMITER;

		// 编译文件目录
		$this->_smarty->compile_dir  = APPLICATION_PATH.'/Data/Temp';

		// 是否强行编译
		$this->_smarty->force_compile = $appCfg['debug'];

		// 赋于视图中全局的对象
		$this->_smarty->assign('request', QP_Request::getInstance());
	}

	/**
	* 重载接口: 返回模引擎对象
	*
	* @return object
	*/
	public function getEngine()
	{
		return $this->_smarty;
	}

	/**
	 * 重载:返回解析后的视图
	 *
	 * @param string $name 视图名
	 * @return string
	 */
	public function render($name){
		// 设置模块目录
		$this->_smarty->template_dir = $this->getPath();
		$file = $this->_smarty->template_dir.$name;
		// 检测视图文件是否存在
		if(!file_exists($file)){
			require_once QUICKPHP_PATH . '/Exception.php';
			throw new QP_Exception("视图不存在:$file");
		}

		// 是否有全局设置
		$globalVal = QP_View::getGlobal();
		if($globalVal){
			$this->assign($globalVal);
		}

		// 把所有变量设置到 SMARTY 中
		$this->_smarty->assign($this->_vars);
		return $this->_smarty->fetch($file);
	}
}
