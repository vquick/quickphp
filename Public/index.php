<?php
/**
 * 站点统一入口
 *
 * @category QuickPHP
 * @copyright http://www.vquickphp.com
 * @version $Id: index.php 905 2011-05-05 07:43:56Z yuanwei $
 */

// 定义WEB根目录,一般APP中可以用到
define('SITEWEB_PATH', realpath(dirname(__FILE__)));

// 定义框架的根目录,框架中会用到
define('QUICKPHP_PATH', SITEWEB_PATH.'/../QuickPHP');

// 定义APP根目录
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', SITEWEB_PATH . '/../Application');

// 引入框架
require QUICKPHP_PATH.'/Application.php';

// 创建APP并而开始运行,这里可以根据情况装载不同的配置文件
$application = new QP_Application(APPLICATION_PATH . '/Configs/Appconfig.php');
$application->run();
