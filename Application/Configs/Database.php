<?php
/**
 * 数据库配置
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Database.php 905 2011-05-05 07:43:56Z yuanwei $
 */

return array(
	/**
	 * 默认数据库连接,引用 DB 时的默认数据库配置
	 */
	'default'=>array(
		// 主机名或IP
		'host'=>'localhost',
		// 端口,默认为 3306
		'port'=>3306,
		// 是否常连接
		'pconnect'=>false,
		// 数据库字符集
		'charset'=>'utf8',
		// 用户名
		'username'=>'root',
		// 密码
		'password'=>'root',
		//数据库名
		'dbname'=>'test',
	),

	/**
	 * 从数据库连接配置，在大访问量的系统中可以考虑做 M/S 的读写分离方案，如果只有一个DB服务器则该项可以不用配置
	 */
	'slave'=>array(
		// 主机名或IP
		'host'=>'localhost',
		// 端口,默认为 3306
		'port'=>3306,
		// 是否常连接
		'pconnect'=>false,
		// 数据库字符集
		'charset'=>'utf8',
		// 用户名
		'username'=>'root',
		// 密码
		'password'=>'root',
		//数据库名
		'dbname'=>'test',
	),
);