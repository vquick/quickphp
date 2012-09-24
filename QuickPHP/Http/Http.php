<?php
/**
 * HTTP 相关操作
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Http.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
class QP_Http_Http
{
	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	* 下载文件
	* 可以指定下载显示的文件名，并自动发送相应的Header信息
	* 如果指定了content参数，则下载该参数的内容
	* @param string $filename 下载文件名,要是绝对路径
	* @param string $showname 下载时显示的文件名,默认为下载的文件名
	* @param string $content  下载的内容
	* @return void
	*/
	static public function download($filename='', $showname='',$content='',$expire=180)
	{
		//得到下载长度
		if(file_exists($filename)) {
			$length = filesize($filename);
		}elseif($content != '') {
			$length = strlen($content);
		}else {
			throw new QP_Exception('QP_Http_Http 错误：没有设置 $filename 或 $content');
		}
		// 最到显示的下载文件名
		if($showname == ''){
			$showname = $filename;
		}
		$showname = basename($showname);
		// 根据扩展名得到 MIME TYPE
		$infoArr = pathinfo($showname);
		$mimeType = QP_Func_Func::mimeType($infoArr['extension']);
		// 发送Http Header信息 开始下载
		Header("Content-type: $mimeType");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: ".$length);
		Header("Content-Disposition: attachment; filename=\"{$showname}\"");
		// 优先下载指定的内容再下载文件
		if($content == '' )
		{
			$file = @fopen($filename,"r");
			if (!$file)
			{
				throw new QP_Exception('QP_Http_Http 错误：下载文件打开失败 '.$filename);
			}
			// 一次读 1K 内容
			while(! @feof($file)){
				echo @fread($file,1024*1000);
			}
			@fclose($file);
		}else {
			echo($content);
		}
		exit();
	}

	/**
	* 发送HTTP状态头
	*/
	static public function sendStatus($code)
	{
		static $_status = array
		(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',

			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',

			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',

			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded',
		);
		if(array_key_exists($code,$_status)) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
		}
	}

	/**
	* 发送 HTTP AUTH USER 请求
	*
	* 使其弹出一个用户名／密码输入窗口。当用户输入用户名和密码后,脚本将会被再次调用.
	* 这时就可以调用 Http::getAuthUser()方法得到输入的用户名和密码了
	*/
	static public function sendAuthUser($hintMsg,$errorMsg='')
	{
		header("WWW-Authenticate: Basic realm=\"{$hintMsg}\"");
		header('HTTP/1.0 401 Unauthorized');
		exit($errorMsg);
	}

	/**
	* 得到 HTTP AUTH USER 请求后的用户名和密码
	*
	* 如果没有发送该请求该会返回 false,否则返回包含用户名和密码的数组，格式如下:
	* array('user'=>'yuanwei',
	*       'pwd'=>'123456');
	*/
	static public function getAuthUser()
	{
		if (isset($_SERVER['PHP_AUTH_USER']))
		{
			return array('user'=>$_SERVER['PHP_AUTH_USER'],
					'pwd' =>$_SERVER['PHP_AUTH_PW']);
		}else{
			return false;
		}
	}

	/**
	* 设置页面缓存,使表单在返回时不清空
	*/
	static public function setFormCache()
	{
		session_cache_limiter('private,must-revalide');
	}
}
