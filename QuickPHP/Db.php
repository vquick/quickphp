<?php
/**
 * 数据库工厂
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Db.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Db
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
	 * 得到数据库操作对象
	 *
	 * @param string $driverType 驱动类型,可选项有: 'mysql' | 'mysqli' | 'pdo'
	 * @param string $configItem 数据库配置项，具体请看 Application/Configs/Database.php
	 * @return object
	 */
	static public function factory($driverType='mysql', $configItem='default')
	{
		// 对象唯一KEY
		$key = $driverType.$configItem;
		// 判断驱动是否已经生成对应的对象了
		$driverType = ucfirst(strtolower($driverType));
		if(! isset(self::$_instance[$key])){
			// 判断驱动文件是否存在
			$driverFile = QUICKPHP_PATH.'/Db/'.$driverType.'.php';
			if(! file_exists($driverFile)){
				throw new QP_Exception('数据驱动文件不存在:'.$driverFile);
			}

			// 判断配置项是否定义
			$dfCfgFile = APPLICATION_PATH . '/Configs/Database.php';
			$dbConfig = include $dfCfgFile;
			if(! isset($dbConfig[$configItem])){
				throw new QP_Exception("数据库配置项:$configItem 在配置文件中找不到:$dfCfgFile");
			}

			// 得到APP配置
			$appCfg = QP_Sys::getAppCfg();

			// 生成驱动对象
			require $driverFile;
			$class = 'QP_Db_'.$driverType;
			self::$_instance[$key] = new $class($dbConfig[$configItem], $appCfg['debug'], $appCfg['display_error']);
		}
		return self::$_instance[$key];
	}

}
