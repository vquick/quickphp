<?php
/**
 * 请求组件
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Request.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

class QP_Request
{
	/**
	 * 控制器名的默认 GET 参数
	 *
	 */
	const C = 'c';

	/**
	 * 动作方法名的默认 GET 参数
	 *
	 */
	const A = 'a';

	/**
	 * 单例模式
	 *
	 * @var object
	 */
	private static $_instance = null;

	/**
	 * uri 参数
	 *
	 * @var array
	 */
	private $_params = array();

	/**
	 * 构造函数,但该类不能被实例化
	 */
	private function __construct()
	{
		// 处理请求数据
		$_POST = $this->_magicQuotes($_POST);
		$_GET = $this->_magicQuotes($_GET);
		$_REQUEST = $this->_magicQuotes($_REQUEST);

		// 如果 URL模式 不是 standard 则要解析 URI
		$appCfg = QP_Sys::getAppCfg();
		if($appCfg['url_method'] != 'standard'){
			$this->_parseUri();
		}
	}

	/**
	 * 防止对象被复制
	 *
	 */
	private function __clone(){}

	/**
	 * 生成单个实例
	 *
	 * @return Request object
	 */
	static public function getInstance()
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	* 获得参数,注意：这种参数只能当是URL是 'pathinfo' 或 'rewrite' 时才会有
	*
	* @param string $name:域名称,如果为空则返回所有参数数组
	* @param string $default:不存在时的默认值
	* @return mixed
	*/
	public function getParam($name='',$default=null)
	{
		return $name ? (isset($this->_params[$name]) ? $this->_params[$name] : $default) : $this->_params;
	}

	/**
	* 设置参数值
	*
	* @param mixed $name:域名称
	* @param string $value:值
	*/
	public function setParam($name,$value='')
	{
		if (!is_array($name))
		{
			$this->_params[$name] = $this->_magicQuotes($value);
		}else{
			$this->_params = array_merge($this->_params, $this->_magicQuotes($name));
		}
	}

	/**
	* 得到输入的参数变量 $_SERVER['argv']数组中的变量
	*
	* @param int $offset:域名称,空则返回整个数组
	* @param string $default:不存在时的默认值
	* @return mixed
	*/
	public function argv($offset=null,$default=null)
	{
		if (null === $offset)
		{
			return $_SERVER['argv'];
		}else{
			return (isset($_SERVER['argv']) && isset($_SERVER['argv'][$offset])) ? $_SERVER['argv'][$offset] : $default;
		}
	}

	/**
	* 得到文件域 $_FILES 数组
	*
	* @param int $offset:域名称
	* @return mixed
	*/
	public function files($name='')
	{
		if (!$name){
			return $_FILES;
		}else{
			return isset($_FILES[$name]) ? $_FILES[$name] : null;
		}
	}

	/**
	 * 得到全局变量 $_SERVER 的值
	 *
	 * @param string $name
	 * @param string $default:不存在时的默认值
	 * @return string
	 */
	public function server($name,$default=null)
	{
		return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
	}

	/**
	* 设置 POST 的值
	*
	* @param mixed $name:域名称
	* @param string $value:值
	*/
	public function setPost($name,$value='')
	{
		if (!is_array($name))
		{
			$_POST[$name] = $this->_magicQuotes($value);
		}else{
			$_POST = array_merge($_POST, $this->magicQuotes($name));
		}
	}

	/**
	* 获得 POST 数据
	*
	* @param string $name:域名称,如果为空则返回整个 $_POST 数组
	* @param string $default:不存在时的默认值
	* @return mixed
	*/
	public function getPost($name='',$default=null)
	{
		return $name ? (isset($_POST[$name]) ? $_POST[$name] : $default) : $_POST;
	}

	/**
	* 设置 GET 的值
	*
	* @param mixed $name:域名称
	* @param string $value:值
	*/
	public function setGet($name,$value='')
	{
		if (!is_array($name))
		{
			$_GET[$name] = $this->_magicQuotes($value);
		}else{
			$_GET = array_merge($_GET, $this->_magicQuotes($name));
		}
	}

	/**
	* 获得 GET 数据
	*
	* @param string $name:域名称,如果为空则返回整个 $_POST 数组
	* @param string $default:不存在时的默认值
	* @return mixed
	*/
	public function getGet($name='',$default=null)
	{
		return $name ? (isset($_GET[$name]) ? $_GET[$name] : $default) : $_GET;
	}

	/**
	 * 判断当前请求是否为 POST
	 *
	 * @return bool
	 */
	public function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	/**
	 * 返回上一个页面的 URL 地址(来源)
	 *
	 * @return string
	 */
	public function frontUrl()
	{
		return isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
	}

	/**
	 * 返回当前页面的 URL 地址
	 *
	 * @return string
	 */
	public function currentUrl()
	{
		$http = isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"] ? 'https' : 'http';
		$http .= '://';
		return $http.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}


	/**
	* 取得当前请求使用的协议
	*
	* 返回值不包含协议的版本。常见的返回值是 http。
	* @return string 当前请求使用的协议
	*/
	public function protocol()
	{
		static $protocol;
		if (is_null($protocol))
		{
			list ($protocol) = explode('/', $_SERVER['SERVER_PROTOCOL']);
		}
		return strtolower($protocol);
	}

	/**
	* 确定请求的完整 URL
	*
	* 几个示例：
	*
	* <ul>
	*   <li>请求 http://www.example.com/index.php?controller=posts&action=create</li>
	*   <li>返回 /index.php?controller=posts&action=create</li>
	* </ul>
	* <ul>
	*   <li>请求 http://www.example.com/news/index.php?controller=posts&action=create</li>
	*   <li>返回 /news/index.php?controller=posts&action=create</li>
	* </ul>
	* <ul>
	*   <li>请求 http://www.example.com/index.php/posts/create</li>
	*   <li>返回 /index.php/posts/create</li>
	* </ul>
	* <ul>
	*   <li>请求 http://www.example.com/news/show/id/1</li>
	*   <li>返回 /news/show/id/1</li>
	* </ul>
	*
	* 此方法参考 Zend Framework 实现。
	*
	* @return string 请求的完整 URL
	*/
	public function requestUri()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			$uri = $_SERVER['HTTP_X_REWRITE_URL'];
		}
		elseif (isset($_SERVER['REQUEST_URI']))
		{
			$uri = $_SERVER['REQUEST_URI'];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']))
		{
			$uri = $_SERVER['ORIG_PATH_INFO'];
			if (! empty($_SERVER['QUERY_STRING']))
			{
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}else{
			$uri = '';
		}
		return $uri;
	}

	/**
	* 返回服务器响应请求使用的端口
	*
	* 通常服务器使用 80 端口与客户端通信，该方法可以获得服务器所使用的端口号。
	*
	* @return string 服务器响应请求使用的端口
	*/
	public function serverPort()
	{
		static $server_port = null;
		if ($server_port) return $server_port;
		if (isset($_SERVER['SERVER_PORT']))
		{
			$server_port = intval($_SERVER['SERVER_PORT']);
		}else{
			$server_port = 80;
		}

		if (isset($_SERVER['HTTP_HOST']))
		{
			$arr = explode(':', $_SERVER['HTTP_HOST']);
			$count = count($arr);
			if ($count > 1)
			{
				$port = intval($arr[$count - 1]);
				if ($port != $server_port)
				{
					$server_port = $port;
				}
			}
		}
		return $server_port;
	}


	/**
	* 判断 HTTP 请求是否是通过 XMLHttp 发起的
	*
	* @return boolean
	*/
	public function isAjax()
	{
		return strtolower($this->header('X_REQUESTED_WITH')) == 'xmlhttprequest';
	}

	/**
	* 判断 HTTP 请求是否是通过 Flash 发起的
	*
	* @return boolean
	*/
	public function isFlash()
	{
		return strtolower($this->header('USER_AGENT')) == 'shockwave flash';
	}

	/**
	* 返回请求的原始内容
	*
	* @return string
	*/
	public function rawBody()
	{
		$body = file_get_contents('php://input');
		return (strlen(trim($body)) > 0) ? $body : false;
	}

	/**
	* 返回 HTTP 请求头中的指定信息，如果没有指定参数则返回 false
	*
	* @param string $header 要查询的请求头参数
	*
	* @return string 参数值
	*/
	public function header($header)
	{
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp])) return $_SERVER[$temp];
		if (function_exists('apache_request_headers'))
		{
			$headers = apache_request_headers();
			if (!empty($headers[$header])) return $headers[$header];
		}
		return false;
	}

	/**
	 * 得到访问都的IP
	 *
	 */
	public function getClientIp(){
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = "unknown";
		return($ip);
	}
	/**
	 * 根据设置处理魔术引用
	 *
	 * @param string|array $value 值
	 * @return unknown
	 */
	private function _magicQuotes($value)
	{
		// 是否打开了魔术引用
		if (get_magic_quotes_gpc())
		{
			$value = is_array($value) ? array_map(array($this,__FUNCTION__), $value) : stripslashes($value);
		}
		return $value;
	}


	/**
	 * 解析 URI 中的参数
	 *
	 */
	private function _parseUri()
	{
		$uri = $this->requestUri();
		// 去掉所有的 GET 参数
		$p = strpos($uri, '?');
		if(false !== $p){
			$uri = substr($uri,0,$p);
		}

		// 如果为空则不用解析了
		if($uri == ''){
			return;
		}

		// 兼容 pathinfo 如: /index.php/index/test?id=123
		$uri = str_replace('/index.php','',$uri);
		if($uri == '' || $uri == '/'){
			return;
		}

		// 把 前后 '/' 都去掉
		$ulen = strlen($uri);
		if($uri[0] == '/'){
			$uri = substr($uri, 1, $ulen-1);
		}
		$ulen = strlen($uri);
		if($uri[$ulen-1] == '/'){
			$uri = substr($uri, 0, $ulen-1);
		}

		// 分解参数,到这时 $uri 应该是这种形式了: index/test
		// 如果小于两个参数则说明只有控制器和动作
		$params = explode('/',$uri);
		$plen = count($params);
		// 只有控制器，动作则使用默认的
		if($plen < 2){
			$params[] = QP_Controller::DEFAULT_ACTION;
		}

		// 设置默认参数 控制器 和 动作
		$this->_params['controller'] = $params[0];
		$this->_params['action'] = $params[1];

		// 没有其它参数就不用解析了
		$params = array_slice($params,2);
		$plen = count($params);
		if($plen < 2){
			return;
		}

		// 解析其它参数 id/1/name/yuanwei
		for($i=2; $i<=$plen; $i+=2){
			$this->_params[$params[$i-2]] = $params[$i-1];
		}
	}
}
