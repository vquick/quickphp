<?php
/**
 * 配置文件读取引擎工具
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Config.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
/* 
 * 注意以下事项：
 * 1:配置文件必需放在 Application/Configs 目录下
 * 2:配置文件名中不能有 '.' 字符,开头字母要大写,如: "Appconfig.php"
 * 3:配置文件要按以下格式书写,如：
 * <?php
 * return array(
 * 	'id'=>10,
 * 	'rows'=>array('id'=>100,'name'=>'V哥')
 * );
 * 
 * @example 
 * $cfg = QP_Sys::load('config');
 * var_dump($cfg->get('application'));
 * var_dump($cfg->get('application.id'));
 * var_dump($cfg->et('application.rows.name'));
 */

class QP_Config_Config
{
	/**
	 * 防止同一文件重复 include()
	 */
	static private $_cfgFileAry = array();
	
	/**
	 * 读取配置项
	 *
	 * @param string $cfgItem 配置项,如: "application"或"application.id"
	 * @return unknown
	 */
	public function get($cfgItem)
	{
		// 分解得到文件名和数据项键名
		$param = explode('.',$cfgItem);
		$len = count($param);
		$cfgValue = $this->_loadfile($param[0]);
		
		// 只传了文件名
		if($len == 1){
			return $cfgValue;
		}
		// 有数组键名有情况，如 "application.id"
		$value = $cfgValue;
		for($i=1; $i<$len; ++$i){
			$key = $param[$i];
			if(isset($value[$key])){
				$value = $value[$key];
			}else{
				$value = '';
				break;
			}
		}
		return $value;
	}
	
	/**
	 * 为配置添加/更新其值
	 *
	 * @param unknown_type $cfgName 配置名,如: $cfgName='application' 代表着操作 application.php
	 * @param unknown_type $cfgArr 配置值,如果没有则添加，有则覆盖。
	 */
	public function set($cfgName,$cfgArr){
		$key = ucfirst(strtolower($cfgName));
		$cfgValue = $this->_loadfile($cfgName);
		self::$_cfgFileAry[$key] = array_merge($cfgValue,$cfgArr);
	}
	
	/**
	 * 载入配置文件并返回值
	 *
	 */
	private function _loadfile($file){
		$file = ucfirst(strtolower($file));
		// 是否载入过了
		if(! isset(self::$_cfgFileAry[$file])){
			// 载入配置文件
			$cfgFile = APPLICATION_PATH.'/Configs/'.$file.'.php';
			if(!file_exists($cfgFile)){
				throw new QP_Exception("文件不存在:$cfgFile");
			}
			$cfgValue = self::$_cfgFileAry[$file] = include($cfgFile);
		}else{
			$cfgValue = self::$_cfgFileAry[$file];
		}
		return $cfgValue;
	}
	
}//End Class
