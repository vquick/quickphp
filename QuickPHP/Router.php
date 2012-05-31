<?php
/**
 * 框架路由组件
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Router.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Router
{
	/**
	 * 保存所有路由数据
	 *
	 * @var array
	 */
	private static $_router = array();

	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	 * 设置路由
	 *
	 * @param string $name 名称
	 * @param array $routerData 数据,格式如下:
	 * array(
	 * 	'uri'=>'user/(<id>)',
	 * 	'bind'=>array('id'=>'\d+'),
	 * 	'controller'=>'index',
	 * 	'action'=>'test',
	 * )
	 */
	static public function set($name, $routerData=array()){
		self::$_router[$name] = $routerData;
	}

	/**
	 * 得路由配置
	 *
	 * @param string $name 名称,如果名称为空则返回所有路由配置
	 * @return array
	 */
	static public function get($name=''){
		return $name=='' ? self::$_router : (isset(self::$_router[$name]) ? self::$_router[$name] : false);
	}

	/**
	 * 返回匹配 URI 的路路由配置,这个方法框架会调用
	 *
	 * @param str $uri
	 */
	static public function matches($uri){
		// 得到所有设置的路由URI
		$regexUri = array();
		foreach (self::$_router as $name=>$param){
			// 如果没有设置 bind 字段
			if(! isset($param['bind'])){
				$regexUri[$name] = $param['uri'];
				continue;
			}
			// 将 uri 和 bind 合并
			$search = $replace = array();
			foreach ($param['bind'] as $key=>$value){
				$search[] = '<'.$key.'>';
				$replace[] = $value;
			}
			$regexUri[$name] = str_replace($search, $replace, $param['uri']);
		}

		// 遍历 URI 匹配，找到第一个即认为找到了
		foreach ($regexUri as $name=>$regUri){
			// 合并成正则表达式
			$regUri = '/'.str_replace('/',"\\/",$regUri).'/';

			// 匹配
			$bool = preg_match($regUri, $uri, $match);

			// 如果没有找到则直接下一个继续
			if(! $bool){
				continue;
			}

			// 如果找到了则就返回找到的第一个配置
			// print_r($match);
			// 得到匹配成功的配置
			$router = self::$_router[$name];

			// 如果没有搜索到子项则直接返回
			if(count($match) == 1){
				return $router;
			}

			// 搜索到了则要 设置 Request 的 param()
			$param = array();
			$i = 0;
			$keyArr = array_keys($router['bind']);
			foreach ($keyArr as $key){
				++$i;
				$param[$key] = isset($match[$i]) ? $match[$i] : '';
			}
			// print_r($param);
			QP_Request::getInstance()->setParam($param);

			// 走了，其它配置不管了
			return $router;
		}
		return array();
	}

	/**
	 * 判断是否使用了路由,这个方法框架会调用
	 *
	 * @return boolean
	 */
	static public function isUseRouter(){
		return self::$_router ? true : false;
	}
}

