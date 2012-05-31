<?php
/**
 * DB 抽象类,子类必需都继承它
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Abstract.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 引入视图接口和基类
 */
require QUICKPHP_PATH . '/Db/Interface.php';
require QUICKPHP_PATH . '/Db/Base.php';

abstract class QP_Db_Abstract extends QP_Db_Base implements QP_Db_Interface
{
	/**
	 * 是否打开调试信息
	 *
	 * @var unknown_type
	 */
	protected $_debug = false;

	/**
	 * 是否显示错误信息
	 *
	 * @var unknown_type
	 */
	protected $_showError = false;
	
	/**
	 * 数据库配置,具体请看 Application/Database.php
	 *
	 * @var array
	 */
	protected $_config = array();
	
	/**
	 * DB连接句柄
	 *
	 * @var resource
	 */
	protected $_link = null;
	
	/**
	 * 查询句柄
	 *
	 * @var resource
	 */
	protected $_query = null;
	
	/**
	 * 执行SQL的总时间
	 * 
	 * @var float
	 */
	protected $_totalTime = 0;
	
	/**
	 * 记录SQL调试时间
	 *
	 * @param string $sql 执行的SQL语句
	 * @param float $execTime 执行的时间
	 */
	protected function _setDebugInfo($sql, $execTime){
		// 总时间累加
		$this->_totalTime += $execTime;
		// 是否设置了调试选项
		$cfgObj = QP_Registry_Registry::getInstance();
		if(! $cfgObj->keyExists('DB_DEBUG')){
			$cfgObj->set('DB_DEBUG', array(
				array(
				'sql'=>$sql,
				'execTime'=>$execTime,
				'totalTime'=>$this->_totalTime,
				),
			));
		}else{
			$data = $cfgObj->get('DB_DEBUG');
			$data[] = array(
				'sql'=>$sql,
				'execTime'=>$execTime,
				'totalTime'=>$this->_totalTime,
				);
			$cfgObj->set('DB_DEBUG',$data);	
		}
	}
	
	/**
	 * 设置数据库连接字符集
	 *
	 */
	protected function _setCharset(){
		$this->query('SET NAMES '.$this->_config['charset']);
	}
	
	/**
	 * 构造函数
	 *
	 * @param array $config 数据库配置项,详细请看 Application/Configs/Database.php
	 * @param boolean $isDebug 是否打开调用信息
	 * @param boolean $showError 是否显示错误信息
	 */
	public function __construct($config=array(), $isDebug=false, $showError=false){
		$this->_config = $config;
		$this->_debug = $isDebug;
		$this->_showError = $showError;
	}
	
	/**
	 * 执行任何SQL语句,推荐使用
	 *
	 * 说明:$sql语句中可以传参数,格式如:"select * from user where userid=:uid and username=:name" 其中: ":uid"和":name" 表示参数变量
	 *     则必需定义$bind为: $bind=array('uid'=>3,
	 *                                  'name'=>'yuanwei')
	 *     表示$sql中 :uid 的值为3, :name 的值为'yuanwei'
	 *
	 * 注意:SQL中的参数只能用于 WHERE 条件中
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return bool
	 */
	public function execute($sql,$bind=array())
	{
		$sql = $this->_parseBind($sql, $bind);
		// 如果打开了调试
		if($this->_debug){
			$beginTime = microtime(true);
			$bool = $this->query($sql);
			$endTime = microtime(true);
			// 记录调试信息
			$this->_setDebugInfo($sql, $endTime-$beginTime);
		}else{
			$bool = $this->query($sql);
		}
		return $bool;
	}		
	
	
	/**
	 * 返回处理后的查询二维结果集,返回的结果格式为:
	 *
	 * 如果SQL的结果集为:
	 *   -uid- -name- -age-  (字段名)
	 *    u1    yuan   20    (第一行记录)
	 *    u2    zhan   19    (第二行记录)
	 *
	 * 则则函数返回的数组值为:
	 *   array('u1'=>array('uid'=>'u1','name'=>'yuan','age'=>20),
	 *         'u2'=>array('uid'=>'u2','name'=>'zhan','age'=>19)
	 *        )
	 *
	 * 说明:有关 $sql和$bind 的用法请看 execute()
	 * 
	 * 注意：为了效率,子类可重载该方法
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchAssoc($sql,$bind=array())
	{
		$data = array();
		$result = $this->fetchAll($sql,$bind);
		foreach ($result as $row){
			$data[current($row)] = $row;
		}
		return $data;
	}

	/**
	 * 执行SQL并返回结果集的第一行(一维数组)
	 *
	 * 说明:有关 $sql和$bind 的用法请看 execute()
	 *
	 * 注意：为了效率,子类可重载该方法
	 * 
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchRow($sql,$bind=array())
	{
		$result = $this->fetchAll($sql,$bind);
		return current($result);
	}

	/**
	 * 返回结果集中第一列的所有值(一维数组)
	 *
	 * 说明:有关 $sql和$bind 的用法请看 execute()
	 *
	 * 注意：为了效率,子类可重载该方法
	 * 
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchCol($sql,$bind=array())
	{
		$data = array();
		$result = $this->fetchAll($sql,$bind);
		foreach ($result as $row){
			$data[] = current($row);
		}
		return $data;
	}

	/**
	 * 执行SQL并返回结果集中第一行第一列的值
	 *
	 * 说明:有关 $sql和$bind 的用法请看 execute()
	 *
	 * 注意：为了效率,子类可重载该方法
	 * 
	 * @param string $sql
	 * @param array $bind
	 * @return array
	 */
	public function fetchOne($sql,$bind=array())
	{
		$result = $this->fetchAll($sql,$bind);
		if($result){
			return current(current($result));
		}else{
			return false;
		}
	}
	
	/**
	 * 组合各种条件查询并返回结果集
	 *
	 * 说明:$where 可以是字符串或数组,如果定义为数组则格式有如下两种:
	 *      $where = array('id'=>1,
	 *                     'name'=>'yuanwei');
	 *      解析后条件为: "id=1 AND name='yuanwei'"
	 *
	 *      $where = array('id'=>array('>='=>1),
	 *                     'name'=>array('like'=>'%yuanwei%'));
	 *      解析后条件为: "id>=1 AND name LIKE '%yuanwei%'"
	 *
	 * 注意:#where 中的条件解析后都是用 AND 连接条件,其它形式请直接用字符串的方法传值
	 *
	 * @param string || array $fields :字段名 'uid,name' || array('uid','name') 支持别名方式
	 * @param string $tables :表
	 * @param mixed $where   :条件
	 * @param string $order  :排序字段
	 * @param string $limit  :返回记录行,格式 "0,10"
	 * @param string $group  :分组字段
	 * @param string $having :筛选条件
	 * @return array
	 */
	public function select($fields,$tables,$where='',$order='',$limit='',$group='',$having='')
	{
		$sql = 'SELECT '.$this->_parseFields($fields).' FROM '.$tables;
		if ($where != ''){
			$sql .= ' WHERE '.$this->_parseWhere($where);
		}
		if ($group != ''){
			$sql .= ' GROUP BY '.$group;
		}
		if ($having != ''){
			$sql .= ' HAVING '.$having;
		}
		if ($order != ''){
			$sql .= ' ORDER BY '.$order;
		}
		if ($limit != ''){
			$sql .= ' LIMIT '.$limit;
		}
		return $this->fetchAll($sql);
	}
	
	/**
	* 更新记录,执行 UPDATE 操作
	*
	* 说明: $arrSets 格式如下:
	*      $arrSets = array('uid'=>1,
	*                       'name'=>'yuanwei');
	*
	* 解析后SET为: "uid=1,name='yuanwei'"
	*
	* @param string $table  :表
	* @param array $arrSets :设置的字段值
	* @param mixed $where   :条件,详细请看 Select()成员
	* @param string $order  :排序字段
	* @param int $limit     :记录行
	 * @param string $group  :分组字段
	* @return boolean
	*/
	public function update($table,$arrSets,$where='',$order='',$limit='',$group='')
	{
		$sqlSet = $this->_parseUpdateSet($arrSets);
		$sql = sprintf("UPDATE %s SET %s",$table,$sqlSet);
		if ($where != '') $sql .= ' WHERE '.$this->_parseWhere($where);
		if ($order != '') $sql .= ' ORDER BY '.$order;
		if ($group != '') $sql .= ' GROUP BY '.$group;
		if ($limit != '') $sql .= ' LIMIT '.$limit;
		return $this->execute($sql);
	}

	/**
	* 插入记录,执行 INSERT 操作
	*
	* 说明:有关 $arrSets 数组的定义请看: Update()成员
	*
	* @param string $table  :表名
	* @param array $arrSets :插入的字段
	* @param boolean $replace : 是否采用 REPLACE INTO 的方式插入记录
	* @return int
	*/
	public function insert($table,$arrSets,$replaceInto=false)
	{
		$ret = $this->_parseInsertSet($arrSets);
		$insertOpt = $replaceInto ? 'REPLACE' : 'INSERT';
		$sql = sprintf("%s INTO %s(%s) VALUES(%s)",$insertOpt,$table,$ret['key'],$ret['val']);
		return $this->execute($sql);
	}

	/**
	 * 删除记录,执行 DELETE 操作,返回删除的记录行数
	 *
	 * @param string $table :表
	 * @param mixed $where  :条件,详细请看 Select()成员
	 * @param string $order :排序字段
	 * @param string $limit :记录行
	 * @param string $group :分组
	 */
	public function delete($table,$where,$order='', $limit='',$group='')
	{
		$sql = "DELETE FROM $table";
		if ($where != '') $sql .= ' WHERE '.$this->_parseWhere($where);
		if ($order != '') $sql .= ' ORDER BY '.$order;
		if ($group != '') $sql .= ' GROUP BY '.$group;
		if ($limit != '') $sql .= ' LIMIT '.$limit;
		return $this->execute($sql);
	}

	/**
	 * 求记录数
	 *
	 * 说明:如果是求表的所有记录(没有WHERE),对于MyISAM表 $countField 请用 '*',否则请指定字段名
	 *
	 * @param string $table      :表
	 * @param mixed $where       :条件
	 * @param string $countField :COUNT字段名
	 * @param string $group      :分组
	 * @return int
	 */
	public function Count($table,$where='',$countField='COUNT(*)',$group='')
	{
		$sql = sprintf("SELECT %s FROM %s",$countField,$table);
		if ($where != '') $sql .= ' WHERE '.$this->_parseWhere($where);
		if ($group != '') $sql .= ' GROUP BY '.$group;
		return $this->fetchOne($sql);
	}

	/**
	 * 开始事务
	 * @return bool
	 */
	public function beginTransaction()
	{
		$bool = $this->query('SET AUTOCOMMIT=0');
		if(! $bool){
			return false;
		}
		return $this->query('BEGIN');
	}

	/**
	 * 提交事务
	 * @return bool
	 */
	public function commit()
	{
		$bool = $this->query('COMMIT');
		if(! $bool){
			return false;
		}
		return $this->query('SET AUTOCOMMIT=1');		
	}

	/**
	 * 事务回滚
	 * @return bool
	 */
	public function rollBack()
	{
		$bool = $this->query('ROLLBACK');
		if(! $bool){
			return false;
		}
		return $this->query('SET AUTOCOMMIT=1');
	}

	/**
	 * 返回MYSQL系统中当前所有可用的数据库
	 *
	 * @return array
	 */
	public function metaDatabases()
	{
		return $this->fetchCol('SHOW DATABASES');
	}

	/**
	 * 返回数据库中所有的表,如果为空则返回当前数据库中所有的表名
	 *
	 * @param string $dbName :数据库名
	 * @return array
	 */
	public function metaTables($dbName='')
	{
		
		$sql = "SHOW TABLES";
		if ($dbName != '') $sql .= ' FROM '.$dbName;
		return $this->fetchCol($sql);
	}

	/**
	 * 返回指定表的所有字段名
	 *
	 * @param string $table :表名
	 * @return Array
	 */
	public function metaColumnNames($table)
	{
		$sql = 'SHOW COLUMNS FROM '.trim($table);
		return $this->fetchCol($sql);
	}

	/**
	 * 清空表,执行 TRUNCATE TABLE 操作
	 * 
	 * @param string $table:表名称
	 */
	public function clear($table)
	{
		$sql = "TRUNCATE TABLE $table";
		return $this->execute($sql);
	}

	/**
	 * 优化表,执行 OPTIMIZE TABLE 操作
	 * 
	 * @param string $table:表名称,如果表名空则优化库中所有的表
	 */
	public function optimize($table='')
	{
		// 得到表
		if ($table == ''){
			$table = $this->fetchCol('SHOW TABLES');
		}else{
			$table = array($table);
		}
		// 优化表
		foreach ($table as $tab)
		{
			$sql = "OPTIMIZE TABLE $tab";
			$this->execute($sql);
		}
		return true;
	}

	/**
	 * 修复表,执行 REPAIR TABLE 操作
	 * 
	 * @param string $table:表名称,如果表名空则修复库中所有的表
	 */
	public function repair($table='')
	{
		// 得到表
		if ($table == ''){
			$table = $this->fetchCol('SHOW TABLES');
		}else{
			$table = array($table);
		}
		// 修复表
		foreach ($table as $tab)
		{
			$sql = "REPAIR TABLE $tab";
			$this->execute($sql);
		}
		return true;
	}
	
	/**
	 * get 函数系列支持对象联级访问
	 * 
	 * 得到 fetchAll() 结果集,如 $db->getAll($table)->field('uid,username')->where('uid<4')->result();
	 * @param string $table :表名,可以为空， 或者通过 ->table() 来指定
	 * @return SqlObj object
	 */
	public function getAll($table='')
	{
		return $this->_queryObj('fetchAll',$table);
	}
	
	/**
	 * get 函数系列支持对象联级访问
	 * 
	 * 得到 fetchRow() 结果集,如 $db->getAll($table)->field('uid,username')->where('uid<4')->result();
	 * @param string $table :表名,可以为空， 或者通过 ->table() 来指定
	 * @return SqlObj object
	 */
	public function getRow($table='')
	{
		return $this->_queryObj('fetchRow',$table);
	}

	/**
	 * get 函数系列支持对象联级访问
	 * 
	 * 得到 fetchOne() 结果集,如 $db->getAll($table)->field('uid,username')->where('uid<4')->result();
	 * @param string $table :表名,可以为空， 或者通过 ->table() 来指定
	 * @return SqlObj object
	 */
	public function getOne($table='')
	{
		return $this->_queryObj('fetchOne',$table);
	}

	/**
	 * get 函数系列支持对象联级访问
	 * 
	 * 得到 fetchCol() 结果集,如 $db->getAll($table)->field('uid,username')->where('uid<4')->result();
	 * @param string $table :表名,可以为空， 或者通过 ->table() 来指定
	 * @return SqlObj object
	 */
	public function getCol($table='')
	{
		return $this->_queryObj('fetchCol',$table);
	}

	/**
	 * get 函数系列支持对象联级访问
	 * 
	 * 得到 fetchCol() 结果集,如 $db->getAll($table)->field('uid,username')->where('uid<4')->result();
	 * @param string $table :表名,可以为空， 或者通过 ->table() 来指定
	 * @return SqlObj object
	 */
	public function getAssoc($table='')
	{
		return $this->_queryObj('fetchAssoc',$table);
	}
	
	/**
	 * 得到查询对象
	 * 
	 * @param $fetchType fetch类型
	 * @param $table 表名，可以为空
	 */
	private function _queryObj($fetchType,$table=''){
		require_once QUICKPHP_PATH . '/Db/Query.php';
		return new QP_Db_Query($this,$fetchType,$table);
	}
	
	/**
	 * 数据库连接或查询错误处理
	 *
	 * @param string $errorMsg 
	 */
	protected function _DBError($errorMsg){
		// 如果显示错误则直接抛出异常
		if($this->_showError){
			throw new QP_Exception($errorMsg, QP_Exception::EXCEPTION_DB_ERROR);
		}else{
			// 否则就写日志
			QP_Sys::log($errorMsg,'db');			
		}
	}
}
