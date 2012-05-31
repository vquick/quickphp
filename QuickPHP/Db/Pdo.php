<?php
/**
 * PDO MYSQL 数据库驱动层
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Pdo.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入DB抽象类
 */
require QUICKPHP_PATH . '/Db/Abstract.php';

class QP_Db_Pdo extends QP_Db_Abstract {

	/**
	 * 结果集的模式
	 *
	 */
	const FETCH_MODE = PDO::FETCH_ASSOC;
	
	/**
	 * 构造函数
	 * 
	 * @see QP_Db_Abstract::__construct()
	 */
	public function __construct($config=array(), $isDebug=false)
	{
		parent::__construct($config, $isDebug);
		// 检测有没有安装 PDO_MYSQl 支持
		if(! in_array('mysql', PDO::getAvailableDrivers()))
		{
			throw new QP_Exception('系统错误：没有安装 PDO MYSQL 支持.');
		}
		
		// 是否打开常连接
		$dns = 'mysql:host='.$this->_config['host'].';port='.$this->_config['port'];
		$dns .= ';dbname='.$this->_config['dbname'].';charset='.$this->_config['charset'];
		$driverOptions = array();
		if($this->_config['pconnect']){
			$driverOptions = array(PDO::ATTR_PERSISTENT=>true);
		}
		
		// 连接DB
		if($this->_debug){
			$beginTime = microtime(true);
			$this->_link = new PDO($dns, $this->_config['username'], $this->_config['password'], $driverOptions);
			$endTime = microtime(true);
			// 记录调试信息
			$this->_setDebugInfo('Connect DB Server.', $endTime-$beginTime);
		}else{
			$this->_link = new PDO($dns, $this->_config['username'], $this->_config['password'], $driverOptions);
		}

		// 是否连接成功
		if(! $this->_link){
			$this->_DBError('数据库连接失败，请检查你的数据库配置是否正确:'.$this->errorMsg());
		}
		
		// 设置字符集 注意：不知为什么 DNS 中的 charset 属性对MYSQL没有作用.
		$this->_setCharset();
		
		// 强制列名是小写
		$this->_link->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

		// 错误级别  PDO::ERRMODE_EXCEPTION  抛出异常
		//          PDO::ERRMODE_WARNING    显示警告错误.
		//          PDO::ERRMODE_SILENT     不显示错误信息，只显示错误码.(只有这个错处框架才好自行处理)
		$this->_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
	}
	
	/**
	 * 重载方法：执行 SQL
	 *
	 * @param unknown_type $sql
	 * @param unknown_type $bind
	 */
	public function query($sql){
		$this->_query = $this->_link->query($sql);
		if(! $this->_query){
			$this->_DBError('SQL查询出错: ['.$sql.'] '.$this->errorMsg());
		}
		return $this->_query ? true : false;
	}
	
	/**
	 * 执行SQL并返回所有结果集
	 *
	 * 说明:有关 $sql和$bind 的用法请看 DB_Msql::Execute()
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchAll($sql,$bind=array())
	{
		$this->execute($sql,$bind);
		return $this->_query->fetchAll(self::FETCH_MODE);		
	}
	
	/**
	 * 重载方法：为了提高效率
	 *
	 * @see QP_Db_Abstract()
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchAssoc($sql,$bind=array()){
		$result = array();
		$this->execute($sql,$bind);
		while ($row =  $this->_query->fetch(self::FETCH_MODE)){
			$result[current($row)] = $row;
		}
		return $result;				
	}
	
	/**
	 * 重载方法：为了提高效率
	 *
	 * @see QP_Db_Abstract()
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchRow($sql,$bind=array()){
		$this->execute($sql,$bind);
		return $this->_query->fetch(self::FETCH_MODE);
	}	
	
	/**
	 * 重载方法：为了提高效率
	 *
	 * @see QP_Db_Abstract()
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchCol($sql,$bind=array()){
		$this->execute($sql,$bind);
		return $this->_query->fetchAll(PDO::FETCH_COLUMN);
	}

	/**
	 * 重载方法：为了提高效率
	 *
	 * @see QP_Db_Abstract()
	 * @param string $sql
	 * @param array $bind
	 * @return mixed 
	 */
	public function fetchOne($sql,$bind=array()){
		$this->execute($sql,$bind);
		return $this->_query->fetchColumn();
	}	
	
	/**
	 * 返回最后执行 Insert() 操作时表中有 auto_increment 类型主键的值
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		return $this->_link->lastInsertId();
	}
	
	/**
	 * 最后 DELETE UPDATE 语句所影响的行数
	 *
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->_query->rowCount();
	}	
	
	/**
	 * 返回调用当前查询后的结果集中的记录数
	 */
	public function rowCount()
	{
		return $this->_query->rowCount();
	}	

	/**
	 * 重载方法：返回当前错误序号
	 *
	 * @return int
	 */
	public function errorNo()
	{
		return $this->_link->errorCode();
	}	
	
	/**
	 * 重载方法：返回当前错误信息
	 *
	 * @return string
	 */
	public function errorMsg()
	{
		$ay = $this->_link->errorInfo();
		return $ay ? $ay[2] : '';
	}
		
	/**
	 * 关闭连接
	 */
	public function close()
	{
		$this->_link  = false;
		return true;
	}
	
	/**
	 * 格式化用于数据库的字符串
	 *
	 * 注意: PDO中会自动加 "'"
	 * 
	 * @param string $str
	 * @return string
	 */
	public function escape($str)
	{
		return $this->_link->quote($str);
	}
	
	/**
	 * 重载方法：开始事务
	 * 
	 * @return bool
	 */
	public function beginTransaction()
	{
		return $this->_link->beginTransaction();
	}

	/**
	 * 重载方法：提交事务
	 * 
	 * @return bool
	 */
	public function commit()
	{
		return $this->_link->commit();
	}

	/**
	 * 重载方法：事务回滚
	 * 
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->_link->rollBack();
	}	
	
}
