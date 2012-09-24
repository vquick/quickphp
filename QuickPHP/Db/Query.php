<?php
/**
 * SQL 级联对象查询生成器
 * 
 * 注意：该对象的引用被封装在 QP_Db_Abstract 中的 getAll(),getCol(),getRow() 等 get 系列方法中
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Query.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

class QP_Db_Query extends QP_Db_Base
{
	/**
	 * 当前的DB对象
	 * 
	 * @var object
	 */
	private $_db;
	
	/**
	 * fetch函数类型
	 *
	 * @var string
	 */
	private $_fetchType;
	
	/**
	 * SQL查询数据
	 *
	 * @var array
	 */
	private $_sqlData = array();	
	
	
	/**
	 * 构造函数
	 *
	 * @param string $dbObj 当前的DB对象
	 * @param string $fetchType fetch函数类型
	 * @param string $table 表名
	 */
	public function __construct($dbObj,$fetchType,$table='')
	{
		$this->_db = $dbObj;
		$this->_fetchType = $fetchType;
		$this->_sqlData['table'] = $table;
		$this->_sqlData['field'] = '*';
	}

	/**
	 * 设置表名
	 *
	 * @param string $table
	 * @return $this
	 */
	public function table($table)
	{
		$this->_sqlData['table'] = $table;
		return $this;
	}

	/**
	 * 设置字段
	 *
	 * @param string|array $fields 
	 * @return $this
	 */	
	public function fields($fields)
	{
		$this->_sqlData['field'] = $this->_parseFields($fields);
		return $this;
	}
	
	/**
	 * 设置主键字段名
	 * 
	 * 注意：如果设置了主键字段则 where() 的条件永远是 $priKey = <where>
	 * 
	 * @param string $priKey 字段名 
	 * @return $this
	 */
	public function primary($priKey)
	{
		$this->_sqlData['primary'] = $this->_parseFields($priKey);
		return $this;
	}
	
	/**
	 * 设置查询条件
	 *
	 * @param string|array $where
	 * @return $this
	 */
	public function where($where)
	{
		$this->_sqlData['where'] = $this->_parseWhere($where);
		return $this;
	}
	
	/**
	 * 设置分组
	 *
	 * @param string $group
	 * @return $this
	 */
	public function group($group)
	{
		$this->_sqlData['group'] = $group;
		return $this;
	}
	
	/**
	 * 设置 HAVING
	 *
	 * @param string $having
	 * @return $this
	 */
	public function having($having)
	{
		$this->_sqlData['having'] = $having;
		return $this;
	}

	/**
	 * 设置排序
	 *
	 * @param string $field 字段
	 * @param string $type 排序类型 asc,desc
	 * @return $this
	 */
	public function order($field,$type='')
	{
		$this->_sqlData['order'] = $field;
		if($type != ''){
			$this->_sqlData['order'] .= ' '.$type;
		}
		return $this;
	}
	
	/**
	 * 设置 LIMIT
	 *
	 * @param int $offset 开始的行数
	 * @param int $length 取多少行,如果为 null 设到 $offset 行
	 * @return $this
	 */
	public function limit($offset=0,$length=null)
	{
		$limit = $offset;
		if($length !== null){
			$limit .= ','.$length;
		}
		$this->_sqlData['limit'] = $limit;
		return $this;
	}
	
	/**
	 * 返回查询结果集
	 *
	 * @return mixed
	 */
	public function result()
	{
		// 生成 SQL
		$sql  = 'SELECT ';
		$sql .= $this->_sqlData['field'];
		$sql .= ' FROM ';
		$sql .= $this->_sqlData['table'];
		// 如果设置了主键则值密码是字符或数值
		if(isset($this->_sqlData['where'])){
			$sql .= ' WHERE ';
			if(isset($this->_sqlData['primary'])){
				$sql .= $this->_sqlData['primary']."='{$this->_sqlData['where']}'";
			}else{
				$sql .= $this->_sqlData['where'];
			}
		}
		if(isset($this->_sqlData['group'])){
			$sql .= ' GROUP BY '.$this->_sqlData['group'];
		}
		if(isset($this->_sqlData['having'])){
			$sql .= ' HAVING '.$this->_sqlData['having'];
		}
		if(isset($this->_sqlData['order'])){
			$sql .= ' ORDER BY '.$this->_sqlData['order'];
		}
		if(isset($this->_sqlData['limit'])){
			$sql .= ' LIMIT '.$this->_sqlData['limit'];
		}
		// 执行不同的方法
		$fetch = $this->_fetchType;
		return $this->_db->{$fetch}($sql);
	}
	
}

