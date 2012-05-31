<?php
/**
 * 自定义模型
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Mymodel.php 905 2011-05-05 07:43:56Z yuanwei $
 */
class Model_Mymodel{
	public function db(){
		$db = QP_Db::factory();
		$allTable = $db->fetchCol("show tables");
		echo '数据库中所有的表：<pre>';
		print_r($allTable);
	}
}