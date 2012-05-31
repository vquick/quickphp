<?php
/**
 * 应该程序配置
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Appconfig.php 905 2011-05-05 07:43:56Z yuanwei $
 */
return array(

	/**
	 * 是否显示调试显示
	 * 注意：如果是 ajax 请求时将不会显示调试信息
	 * 建议：开发时打开，发布后关闭
	 */
	'debug'=>true,

	/**
	 * 是否显示PHP错误和DB错误 true:显示 false:写入日志
	 * PHP错误在：Application/Data/Logs/Php
	 * DB错误在：Application/Data/Logs/Db
	 * 建议：开发时打开,发布后关闭
	 */
	'display_error'=>true,

	/**
	 * 允许的PHP错误类型
	 * 注意：定义在这里的PHP错误类别将不会被显示或写入日志
	 * 建议：开发时保持数组为空，可以确保程序的健壮性更好；发布可以考虑允许PHP的提示错误,如: array(E_NOTICE)
	 */
	'allow_error'=>array(),

	/**
	 * 是否显示程序异常 true:显示 false:写入日志(目录在：Application/Data/Logs/App)
	 *
	 * [注意]
	 * 1:如果是 '控制器不存在异常' 或 '动作不存在异常' 将会永远抛出
	 * 2:具体处理方式可看 Application/Controllers/ErrorController.php
	 *
	 * 建议：开发时打开,发布后关闭并且要处理
	 */
	'display_exception'=>true,

	/**
	 * 设置时区,以免出现时间不正确的情况
	 */
	'timezone'=>'Asia/Shanghai',

	/**
	 * 设置编码字符集
	 */
	'charset'=>'utf-8',

	/*
	 * - standard - 标准模式（默认），例如 index.php?c=index&a=test&id=1
	 * - pathinfo - PATHINFO 模式(兼容 standard 模式)，例如 index.php/index/test/id/1
	 * - rewrite  - URL 重写模式(兼容 standard 模式)，例如 /index/test/id/1
	 * - 注意: rewrite 模式需要服务器支持 REWRITE
	 */
	'url_method'=>'standard',
);