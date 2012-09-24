<?php

/**
 * 数据处理基本操作类,引入这个基类主要是为了代码重用
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Base.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
abstract class QP_Db_Base
{
	/**
	 * 解析字段名,防止字段名是关键字
	 */
	protected function _parseField($fieldName)
	{
		$fieldName = trim($fieldName);
		if (strstr($fieldName,' ')===false)
		{
			$fieldName = '`'.$fieldName.'`';
		}
		return $fieldName;
	}
	
	/**
	 * 解析SQL语句中的值定义
	 *
	 * @param string $sql
	 * @param array $bind
	 * @return string
	 */
	protected function _parseBind($sql, $bind=array())
	{
		$searchArr = array();
		$replaceArr = array();
		if (count($bind))
		{
			foreach ($bind as $k=>$v)
			{
				$searchArr[] = ":$k";
				$replaceArr[] = $this->_returnValue($v);
			}
			$sql = str_replace($searchArr,$replaceArr,$sql);
		}
		return $sql;
	}

	/**
	* 根据值的类型返回SQL语句式的值
	*
	* @param unknown_type $val
	* @return unknown
	*/
	protected function _returnValue($val)
	{
		if (is_int($val) || is_float($val)){
			return $val;
		}else{
			$val = addslashes($val);
			return "'$val'";
		}
	}

	/**
	 * 解析查询的字段
	 * @param array || string $fields : 字段数组或字符串
	 */
	 
	protected function _parseFields($fields)
	{
		$fieldList = $spr = '';
		if (is_array($fields) && $fields)
		{
			foreach ($fields as $field)
			{
				$fieldList .= $spr . $this->_parseField($field);
				$spr = ',';
			}
		}else{
			$fieldList = $fields;
		}
		return $fieldList;
	}

	/**
	 * 解析 SQL WHERE 条件
	 *
	 * @param mixed $where
	 * @return string
	 */
	protected function _parseWhere($where)
	{
		$sqlWhere = '1 ';
		if (is_array($where))
		{
			foreach ($where as $k=>$v)
			{
				$sqlWhere .= " AND ".$this->_parseField($k);
				if (is_array($v))
				{
					foreach ($v as $_k=>$_v){
						$sqlWhere .= sprintf(" %s ",strtoupper($_k)).$this->_returnValue($_v);
					}
				}else{
					$sqlWhere .= '='.$this->_returnValue($v);
				}
			}
		}else{
			$sqlWhere = $where;
		}
		return $sqlWhere;
	}


	/**
	 * 解析 UPDATE 操作字段设置
	 *
	 * @param array $arrSet
	 * @return string
	 */
	protected function _parseUpdateSet($arrSet)
	{
		$sqlSet = $spr = '';
		if (is_array($arrSet))
		{
			foreach ($arrSet as $k=>$v)
			{
				$sqlSet .= $spr.$this->_parseField($k).'='.$this->_returnValue($v);
				$spr = ',';
			}
		}else{
			$sqlSet = $arrSet;
		}
		return $sqlSet;
	}

	/**
	 * 解析 INSERT 操作字段设置
	 *
	 * @param array $arrSet
	 * @return array
	 */
	protected function _parseInsertSet($arrSet)
	{
		$Keys = $Values = $spr = '';
		foreach ($arrSet as $k=>$v)
		{
			$Keys .= $spr.$this->_parseField($k);
			$Values .= $spr.$this->_returnValue($v);
			$spr = ',';
		}
		return array('key'=>$Keys,'val'=>$Values);
	}
}