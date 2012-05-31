<?php
/**
 * Mysql 数据库驱动层
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Mysql.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入DB抽象类
 */
require QUICKPHP_PATH . '/Db/Abstract.php';

class QP_Db_Mysql extends QP_Db_Abstract {

	/**
	 * 结果集的模式
	 *
	 */
	const FETCH_MODE = MYSQL_ASSOC;
	
	/**
	 * 构造函数
	 * 
	 * @see QP_Db_Abstract::__construct()
	 */
	public function __construct($config=array(), $isDebug=false, $showError=false)
	{
		parent::__construct($config, $isDebug, $showError);
		// 连接数据库,根据连接选项使用不同的连接方式
		$connectFun = 'mysql_connect';
		if($this->_config['pconnect']){
			$connectFun = 'mysql_pconnect';
		}
		
		$host = $this->_config['host'].':'.$this->_config['port'];
		// 连接DB
		if($this->_debug){
			$beginTime = microtime(true);
			$this->_link = @$connectFun($host, $this->_config['username'], $this->_config['password']);
			$endTime = microtime(true);
			// 记录调试信息
			$this->_setDebugInfo('Connect DB Server.', $endTime-$beginTime);
		}else{
			$this->_link = @$connectFun($host, $this->_config['username'], $this->_config['password']);
		}

		// 是否连接成功
		if(! $this->_link){
			$this->_DBError('数据库连接失败，请检查你的数据库配置是否正确:'.@mysql_error());
		}
		
		// 打开数据库
		if (! @mysql_select_db($this->_config['dbname'], $this->_link)){
			$this->_DBError('打开数据库失败:'.@mysql_error());
		}
		
		// 设置字符集
		$this->_setCharset();
	}
	
	/**
	 * 重载方法：执行 SQL
	 *
	 * @param unknown_type $sql
	 * @param unknown_type $bind
	 */
	public function query($sql){
		$this->_query = @mysql_query($sql,$this->_link);
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
		$result = array();
		$this->execute($sql,$bind);
		while ($row = @mysql_fetch_array($this->_query, self::FETCH_MODE)){
			$result[] = $row;
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
	public function fetchAssoc($sql,$bind=array()){
		$result = array();
		$this->execute($sql,$bind);
		while ($row = @mysql_fetch_array($this->_query, self::FETCH_MODE)){
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
		return @mysql_fetch_array($this->_query, self::FETCH_MODE);
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
		$result = array();
		$this->execute($sql,$bind);
		while ($row = @mysql_fetch_array($this->_query, self::FETCH_MODE)){
			$result[] = current($row);
		}
		return $result;	
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
		$row = @mysql_fetch_array($this->_query, self::FETCH_MODE);
		if($row){
			return current($row);
		}else{
			return false;
		}
	}
	
	/**
	 * 返回最后执行 Insert() 操作时表中有 auto_increment 类型主键的值
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		/**
		 *  [PHP手册]
		 * 
		 * 如果 AUTO_INCREMENT 的列的类型是 BIGINT，则 mysql_insert_id() 返回的值将不正确。
		 * 可以在 SQL 查询中用 MySQL 内部的 SQL 函数 LAST_INSERT_ID() 来替代。
		 * 
		 */
		$id = $this->fetchOne('select last_insert_id()');
		return $id ? $id : 0;
	}
	
	/**
	 * 最后 DELETE UPDATE 语句所影响的行数
	 *
	 * @return int
	 */
	public function affectedRows()
	{
		return @mysql_affected_rows($this->_link);
	}	
	
	/**
	 * 返回调用当前查询后的结果集中的记录数
	 */
	public function rowCount()
	{
		return @mysql_num_rows($this->_query);
	}	

	/**
	 * 重载方法：返回当前错误序号
	 *
	 * @return int
	 */
	public function errorNo()
	{
		return @mysql_errno($this->_link);
	}	
	
	/**
	 * 重载方法：返回当前错误信息
	 *
	 * @return string
	 */
	public function errorMsg()
	{
		return @mysql_error($this->_link);
	}
		
	/**
	 * 关闭连接
	 */
	public function close()
	{
		@mysql_free_result($this->_query);
		@mysql_close($this->_link);
		return true;
	}
	
	/**
	 * 格式化用于数据库的字符串
	 *
	 * 注意:这个函数与PDO中的不一样,它不会自动加 "'"
	 * @param string $str
	 * @return string
	 */
	public function escape($str)
	{
		$str = @mysql_real_escape_string($str,$this->_link);
		return "'$str'";
	}
	
}
