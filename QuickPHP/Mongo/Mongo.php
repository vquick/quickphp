<?php
/**
 * Mongodb 基本操作API，支持基本类似关系统型数据库的操作接口
 *
 * @author V哥
 * @version 1.0
 *
 * [说明]
 *
 * 1：该版本API实现了 Mongodb 中最基本的插入/修改/查询/删除操作的封装
 * 2：其它更高级的操作可通过 $this->getMongo() 得到原生的对象，更多API请自行查阅 Mongo PHP手册,后续版本将会对增加更多的原生API封装
 * 3：该类所有API接口中的 $query 查询参数的使用请以下有关 [查询条件说明文档]
 * 4：如果要存储中文字符，则一定要使用 utf8 的编码．
 * 5：有了本类接口基本可以按关系型数据库的概念完成Mongodb的大部分开发操作，但作者还是强烈建议了解一下 Mongodb 的特性。
 *
 * [查询条件说明文档]
 *
 * 参数：array('id'=>1)
 * 等同：where id=1
 *
 * 参数：array('id'=>1,'num'=>10)
 * 等同：where id=1 and num=10
 *
 * 参数：array('id'=>array($mongo->cmd('>')=>5))
 * 等同：where id>5
 *
 * 参数：array('id'=>array($mongo->cmd('!=')=>5))
 * 等同：where id!=5
 *
 * 参数：array('id'=>array($mongo->cmd('>')=>5, $mongo->cmd('<')=>10))
 * 等同：where id>5 and id<10
 *
 * 参数：array('id'=>array($mongo->cmd('in')=>array(2,5,6)))
 * 等同：where id in (2,5,6)
 *
 * 参数：array('id'=>array($mongo->cmd('%')=>array(2,1)))
 * 等同：where id % 2 = 1
 *
 * 参数：array($mongo->cmd('or') => array( array('id'=>array($mongo->cmd('>')=>5)), array('id'=>array($mongo->cmd('<')=>10)) ) )
 * 等同：where id>5 or id<10
 *
 **/
class QP_Mongo_Mongo
{

	/**
	 * Mongodb 对象句柄
	 *
	 * @var object Mongo
	 */
	private $_mongo = null;

	/**
	 * 当前选择的数据库
	 *
	 * @var object MongoDB
	 */
	private $_db = null;

	/**
	 * 修改器命令前缀
	 *
	 * @var string
	 */
	private $_cmd = '$';

	/**
	 * 查询条件映射关系
	 *
	 * @var array
	 */
	private $_condMap = array
	(
		'<'=>'lt', // id > 1
		'<='=>'lte', // id <= 10
		'>'=>'gt', // id > 5
		'>='=>'gte', // id >= 4
		'!='=>'ne', // id != 4
		'%'=>'mod', // id % 4 = 0
		'in'=>'in', // id in (1,2,3,4)
		'notin'=>'nin',// id not in (1,2,3,4)
		'or'=>'or', // id=1 or id=2
		'not'=>'not', // !(id=1)
	);

	/**
	 * 构造函数
	 *
	 * @param array $config 服务器配置,默认为:
	 * array(
	 * 'host'=>'localhost', // 主机名或IP地址
	 * 'port'=>27017, // 端口
	 * 'cmd'=>'$', // 修改器命令前缀
	 * )
	 */
	public function __construct($config=array('host'=>'localhost','port'=>27017,'cmd'=>'$'))
	{
		$server = sprintf("mongodb://%s:%s", $config['host'], $config['port']);
		$this->_mongo = new Mongo($server, array('connect'=>true));// 立即连接
		// 命令前缀
		if(!isset($config['cmd'])){
			$this->_cmd = ini_get('mongo.cmd');
			if($this->_cmd == ''){
				$this->_cmd = '$';
			}
		}
	}

	/* ==================================== 基本操作接口API　================================= */

	/**
	 * 向集合(表)中插入新文档
	 *
	 * 说明：
	 * 1:类似mysql中的: insert into $colName set id=1,name='name1';
	 *
	 * @param string $colName 集合名
	 * @param array $sets 数据,如: array('id'=>1,'name'=>'name1')
	 * @param boolean $safe 是否安全操作 false:不等待服务器的响应直接返回 true:等待服务器的响应(数据非常重要时推荐)
	 * @param boolean $fsync 操作后是否立即更新到碰盘,默认情况下由服务器决定
	 *
	 * @return boolean
	 */
	public function insert($colName, $sets, $safe=false, $fsync=false)
	{
		$col = $this->_getCol($colName);
		try {
			$col->insert($sets,array('safe'=>$safe,'fsync'=>$fsync));
			return true;
		}catch (MongoCursorException $e){
			return false;
		}
	}

	/**
	 * 保存文档
	 *
	 * 说明：
	 * 1:如果 $sets 中有字段 "_id" 的话，则更新对应的文档；否则插入新文档
	 *
	 * @param string $colName 集合名
	 * @param array $sets 数据,如: array('id'=>1,'name'=>'name1')
	 * @param boolean $safe 是否安全操作 false:不等待服务器的响应直接返回 true:等待服务器的响应(数据非常重要时推荐)
	 * @param boolean $fsync 操作后是否立即更新到碰盘,默认情况下由服务器决定
	 *
	 * @return boolean
	 */
	public function save($colName, $sets, $safe=false, $fsync=false)
	{
		// 处理 '_id' 字段
		$sets = $this->_parseId($sets);
		$ret = $this->_getCol($colName)->save($sets,array('safe'=>$safe,'fsync'=>$fsync));
		return $ret;
	}

	/**
	 * 删除集合中的文档记录
	 *
	 * 说明：
	 * 1：类似mysql中的: delete from $colName where id=1;
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,如果为空数组的话，则会删除所有记录．具体请看 [查询条件说明文档]
	 * @param boolean $delAll 是否删除所以条例查询的记录,默认为 true,当为 false是，类似效果 delete from tab where id=1 limit 1;
	 * @param boolean $safe 是否安全操作 false:不等待服务器的响应直接返回 true:等待服务器的响应(数据非常重要时推荐)
	 * @param boolean $fsync 操作后是否立即更新到碰盘,默认情况下由服务器决定
	 *
	 * @return boolean
	 */
	public function delete($colName,$query=array(),$delAll=true,$safe=false,$fsync=false)
	{
		// 自动处理 '_id' 字段
		$query = $this->_parseId($query);
		// 删除选项
		$options = array(
		'justOne'=>!$delAll,
		'safe'=>$safe,
		'fsync'=>$fsync,
		);
		$col = $this->_getCol($colName);
		return $col->remove($query,$options);
	}

	/**
	 * 删除整个集合
	 *
	 * 说明：
	 * 1：集合中的索引也会被删除
	 *
	 * @param string $colName 集合名
	 *
	 * @return array
	 */
	public function dropCol($colName)
	{
		return $this->_getCol($colName)->drop();
	}

	/**
	 * 更新集合文档记录
	 *
	 * 说明:
	 * 1：类似mysql中的: update $colName set name='mongo' where id=10;
	 *
	 * @param string $colName 集合名
	 * @param array $newDoc 要更新的文档记录
	 * @param array $query 查询条件,如果为空数组则更新所有记录．具体请看 [查询条件说明文档]
	 * @param string $option 操作选项,可选择项如下；
	 *
	 * 'set'：只修改指定的字段（默认值,如果这个键不存在，则创建它。存在则更新）.
	 * 示例: update('user', array('name'=>'mongo'), array('id'=>10));
	 * 类似: update user set name='mongo' where id=10;
	 *
	 * 'inc'：将指定的字段累加/减(如果值为负数则是相减,不存在键则创建。字段类型一定要是数字)
	 * 示例：update('user', array('num'=>1), array('id'=>10), 'inc');
	 * 类似: update user set num=num+1 where id=10;
	 *
	 * 'push'：将文档添加到指定键中（数组），如果键不存在则会自动创建，存在则添加到该键的尾端。
	 * 示例：update('user', array('comm'=>array('commid'=>1,'title'=>'title1')), array('id'=>1), 'push');
	 * 解说：为 id=1 的记录添加一个 comm 的评论字段，该字段对应一个 array('commid'=>1,'title'=>'title1') 的新文档。
	 *
	 * 'pop':将指定键中的文档删除（数组）
	 * 示例：update('user', array('comm'=>array('commid'=>1)), array('id'=>1), 'pop');
	 * 解说：删除 id=1 的记录中 comm 对应的文档集合中 'commid'=>1 对应的文档.
	 *
	 * 'unset':在文档中删除指定的键
	 * 示例：update('user', array('name'=>1), array('id'=>1), 'unset');
	 * 解说: 将 user 集合中将 id=1 对应的文档中的 name 字段删除
	 *
	 * 'pull':删除文档中匹配其值的键
	 * 示例：update('user', array('name'=>'youname'), array('id'=>1), 'pull');
	 * 解说：将 user 集合中将 id=1 对应的文档中的 name='youname' 的字段删除
	 *
	 * 'addToSet':如果值不存在就添加（避免重复添加）
	 * 示例：update('user', array('names'=>'youname'), array('id'=>1), 'addToSet');
	 * 解说：向 user 集合中 id=1 对应的文档中的 names 字段添加 'youname' 这个值(不存在时才添加)
	 *
	 * 'replace'：用 $newDoc 新文档替换 $query 所找到的文档
	 * 示例：update('user', array('newid'=>1,'newnames'=>'name1'), array('id'=>1), 'replace');
	 * 解说：将 user 集合中 id=1 对应的文档用 array('newid'=>1,'newnames'=>'name1') 的新文档替换
	 *
	 * @param boolean $upAll 是否更新找到的所有记录
	 * @param boolean $upsert 如果查询条件不存在时，是否以查询条件和要更新的字段一起新建一个集合
	 * @param boolean $safe 是否安全删除 false:不等待服务器的响应直接返回 true:等待服务器的响应(数据非常重要时推荐)
	 * @param boolean $fsync 操作后是否立即更新到碰盘,默认情况下由服务器决定
	 *
	 * @return boolean
	 */
	public function update($colName,$newDoc,$query=array(),$option='set',$upAll=true,$upsert=false,$safe=false,$fsync=false){
		// 自动处理 '_id' 字段
		$query = $this->_parseId($query);
		// 得到集合
		$col = $this->_getCol($colName);
		// 重新组合新文档
		if($option != 'replace'){
			$newDoc = array($this->cmd($option) => $newDoc);
		}
		// 更新条件
		$options = array(
		'upsert'=>$upsert,
		'multiple'=>$upAll,
		'safe'=>$safe,
		'fsync'=>$fsync,
		);
		return $col->update($query,$newDoc,$options);
	}

	/**
	 * 查询文档集,返回二维数组
	 *
	 * 说明:
	 * 1:类似mysql中的 select * from table
	 *
	 * 示例：select('user');
	 * 类似：select * from user;
	 *
	 * 示例：select('user',array('id','name'));
	 * 类似：select id,name from user;
	 *
	 * 示例：select('user',array('id','name'),array('id'=>1));
	 * 类似：select id,name from user where id=1;
	 *
	 * 示例：select('user',array('id','name'),array('id'=>1),array('num'=>1));
	 * 类似：select id,name from user where id=1 order by num asc;
	 *
	 * 示例：select('user',array('id','name'),array('id'=>1),array('num'=>1),10);
	 * 类似：select id,name from user where id=1 order by num asc limit 10;
	 *
	 * 示例：select('user',array('id','name'),array('id'=>1),array('num'=>1),10,2);
	 * 类似：select id,name from user where id=1 order by num asc limit 2,10;
	 *
	 *
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,具体请看 [查询条件说明文档]
	 * @param array $fields 结果集返回的字段, array():表示返回所有字段 array('id','name'):表示只返回字段 "id,name"
	 * @param array $sort 排序字段, array('id'=>1):表示按id字段升序 array('id'=>-1):表示按id字段降序 array('id'=>1, 'age'=>-1):表示按id升序后再按age降序
	 * @param int $limit 取多少条记录
	 * @param int $skip 跳过多少条(从多少条开始)
	 *
	 * @return array
	 */
	public function select($colName,$query=array(),$fields=array(),$sort=array(),$limit=0,$skip=0)
	{
		// 得到集合
		$col = $this->_getCol($colName);
		// 自动处理 '_id' 字段
		$query = $this->_parseId($query);
		// 结果集偏历
		$cursor  = $col->find($query,$fields);
		// 排序
		if($sort){
			$cursor->sort($sort);
		}
		// 跳过记录数
		if($skip > 0){
			$cursor->skip($skip);
		}
		// 取多少行记录
		if($limit > 0){
			$cursor->limit($limit);
		}
		$result = array();
		foreach($cursor  as $row){
			$result[] = $this->_parseArr($row);
		}
		return $result;
	}

	/**
	 * 统计文档记录数
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,具体请看 [查询条件说明文档]
	 * @param int $limit 取多少条记录
	 * @param int $skip 跳过多少条
	 * @return unknown
	 */
	public function count($colName,$query=array(),$limit=0,$skip=0)
	{
		return $this->_getCol($colName)->count($query,$limit,$skip);
	}

	/**
	 * 返回集合中的一条记录(一维数组)
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,具体请看 [查询条件说明文档]
	 * @param array $fields 结果集返回的字段, array():表示返回所有字段 array('id','name'):表示只返回字段 "id,name"
	 *
	 * @return array
	 */
	public function fetchRow($colName,$query=array(), $fields=array())
	{
		// 得到集合名
		$col = $this->_getCol($colName);
		// 自动处理 '_id' 字段
		$query = $this->_parseId($query);
		// 处理结果集
		return $this->_parseArr($col->findOne($query,$fields));
	}

	/**
	 * 返回符合条件的文档中字段的值
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,具体请看 [查询条件说明文档]
	 * @param string $fields 要取其值的字段,默认为 "_id" 字段,类似mysql中的自增主键
	 *
	 * @return mixed
	 */
	public function fetchOne($colName,$query=array(), $fields='_id')
	{
		$ret = $this->fetchRow($colName,$query,array($fields));
		return isset($ret[$fields]) ? $ret[$fields] : false;
	}

	/**
	 * 返回查询文档集合集中指定字段的值(一维数组)
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,具体请看 [查询条件说明文档]
	 * @param string $fields 要取其值的字段,默认为 "_id" 字段,类似mysql中的自增主键
	 *
	 * @return array
	 */
	public function fetchCol($colName,$query=array(), $fields='_id')
	{
		$result = array();
		$list = $this->select($colName,$query,array($fields));
		foreach ($list as $row){
			$result[] = $row[$fields];
		}
		return $result;
	}

	/**
	 * 返回指定下标的查询文档集合(二维数组)
	 *
	 * @param string $colName 集合名
	 * @param array $query 查询条件,具体请看 [查询条件说明文档]
	 * @param string $fields 要取其值的字段,默认为 "_id" 字段,类似mysql中的自增主键
	 *
	 * @return array
	 */
	public function fetchAssoc($colName,$query=array(), $fields='_id')
	{
		$result = array();
		$list = $this->select($colName,$query);
		foreach ($list as $row){
			$key = $row[$fields];
			$result[][$key] = $row;
		}
		return $result;
	}

	/* ==================================== 辅助操作接口API　================================= */

	/**
	 * 返回命令或命令前缀
	 *
	 * @param string $option 命令，如果为空时则返回命令前缀
	 *
	 * @return string
	 */
	public function cmd($option='')
	{
		// 只返回命令前缀
		if($option == ''){
			return $this->_cmd;
		}
		// 如果是操作符
		if(isset($this->_condMap[$option])){
			$option = $this->_condMap[$option];
		}
		return $this->_cmd.$option;
	}

	/**
	 * 选择或创建数据库(注意：新创建的数据库如果在关闭连接前没有写入数据将会被自动删除)
	 *
	 * @param string $dbname 数据库名
	 */
	public function selectDB($dbname)
	{
		$this->_db = $this->_mongo->selectDB($dbname);
	}

	/**
	 * 得到所有的数据库
	 *
	 * @param boolean $onlyName 是否只返回数据库名的数组
	 * @return array
	 */
	public function allDB($onlyName=false)
	{
		$ary = $this->_mongo->listDBs();
		if($onlyName){
			$ret = array();
			foreach ($ary['databases'] as $row){
				$ret[] = $row['name'];
			}
			return $ret;
		}else{
			return $ary;
		}
	}

	/**
	 * 删除数据库
	 *
	 * @return array
	 */
	public function dropDB($dbname)
	{
		return $this->_mongo->dropDB($dbname);
	}

	/**
	 * 关闭连接
	 *
	 */
	public function close()
	{
		$this->_mongo->close();
	}

	/**
	 * 得到 Mongo 原生对象，进行其它更高级的操作，详细请看PHP手册
	 *
	 */
	public function getMongo()
	{
		return $this->_mongo;
	}

	/**
	 * 返回最后的错误信息
	 *
	 * @return array
	 */
	public function getError()
	{
		return $this->_db->lastError();
	}

	/* ======================= 以下为私有方法 ====================== */

	// 解析数据组中的'_id'字段(如果有的话)
	private function _parseId($arr)
	{
		if(isset($arr['_id'])){
			$arr['_id'] = new MongoId($arr['_id']);
		}
		return $arr;
	}

	// 得到集合对象
	private function _getCol($colName)
	{
		return $this->_db->selectCollection($colName);
	}

	// 解析数组中的"_id"并且返回
	private function _parseArr($arr)
	{
		$ret = (array)$arr['_id'];
		//$arr['_idobj'] = $arr['_id'];
		$arr['_id'] = $ret['$id'];
		return $arr;
	}
}//End Class


/* =========== 测试用例,更多API请看文档  ============ */
/*
// 连接 Mongo
$mongo = new MongoPHP(array('host'=>'localhost','port'=>27017));

// 使用 bug 数据库
$mongo->selectDB('bug');

// 向 user 集合(表)中插入文档(记录)
for($i=1; $i<=5; $i++){
	$mongo->insert('user', array('id'=>$i, 'name'=>'yuan'.$i));
}
$mongo->insert('user', array('id'=>5, 'name'=>'yuan5'));

// 查询 user 集合中所有的文档
$ret = $mongo->select('user');
print_r($ret);

// 查询 user 集合中 id=5 的文档(2条记录)
$ret = $mongo->select('user',array('id'=>5));
print_r($ret);

// 查询 user 集合中 id=1 的文档(1条记录)
$ret = $mongo->fetchRow('user',array('id'=>1));
print_r($ret);

// 更新 id=1 的文档记录中 name='newname'
$mongo->update('user',array('name'=>'newname'), array('id'=>1));

// 删除 id=1 的文档记录
$mongo->delete('user',array('id'=>2));
*/
