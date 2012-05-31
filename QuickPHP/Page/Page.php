<?php
/**
 * 分页 工具
 *
 * 该工具可以自动兼容框架所设置的 URL 模式
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Page.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
 * 示例：
 * 
 * $page = QP_sys::load('page');
 * $ret = $page->set(array('sumcount'=>100,'perpage'=>10))->result();
 * QP_Sys::dump($ret);
 * 
 */
class QP_Page_Page
{
	/**
	 * 当前系统的 URL 模式
	 *
	 * @var unknown_type
	 */
	private $_urlMethod = 'standard';

	/**
	 * 请求组件
	 *
	 * @var object
	 */
	private $_request = null;

	/**
	 * 属性设置
	 *
	 * @var array
	 */
	private $_options = array(
		// 总记录数
		'sumcount'=>100,
		// 每页显示多少行记录
		'perpage'=>10,

		// 分页的URL,为空时则自动使用当前的URL
		'url'=>'',
		// 分页时的参数,如果与现有的参数有冲突可以改变这个值
		'tagname'=>'page',
		// '上一页' 显示的字符
		'prev'=>'&lsaquo;&lsaquo;',
		// '下一页' 显示的字符
		'next'=>'&rsaquo;&rsaquo;',
		// 每屏显示多个个分页导航
		'page'=>10,
		// 最多显示多少页 0:不限
		'maxpages'=>0,
		// 是否显示总记录数
		'shownum'=>true,
		// 是否显示跳转文本框
		'showkbd'=>false,
	);

	/**
	 * 构造函数
	 *
	 */
	public function __construct()
	{
		// APP配置
		$appCfg = QP_Sys::getAppCfg();
		$this->_urlMethod = $appCfg['url_method'];
		$this->_request = QP_Request::getInstance();
	}


	/**
	 * 设置属性
	 *
	 * @param array $options 属性设置
	 */
	public function set($options){
		$this->_options = array_merge($this->_options, $options);
		return $this;
	}

	/**
	 * 返回分页信息
	 *
	 * @return array
	 */
	public function result(){
		// 当前第几页
		$curpage = $this->_getCurPage();

		// 解析当前的URL
		$this->_parseUrl();

		$multipage = '';
		$realpages = 1;
		if($this->_options['sumcount'] > $this->_options['perpage']) {
			$offset = 2;
			$realpages = @ceil($this->_options['sumcount'] / $this->_options['perpage']);
			$pages = $this->_options['maxpages'] && $this->_options['maxpages'] < $realpages ? $this->_options['maxpages'] : $realpages;

			if($this->_options['page'] > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $curpage - $offset;
				$to = $from + $this->_options['page'] - 1;
				if($from < 1) {
					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $this->_options['page']) {
						$to = $this->_options['page'];
					}
				} elseif($to > $pages) {
					$from = $pages - $this->_options['page'] + 1;
					$to = $pages;
				}
			}

			$multipage = ($curpage - $offset > 1 && $pages > $this->_options['page'] ? '<a href="'.$this->_options['url'].'1" class="first">1 ...</a>' : '').
				($curpage > 1 ? '<a href="'.$this->_options['url'].($curpage - 1).'" class="prev">'.$this->_options['prev'].'</a>' : '');
			for($i = $from; $i <= $to; $i++) {
				$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' :
					'<a href="'.$this->_options['url'].$i.($i == $pages ? '' : '').'">'.$i.'</a>';
			}

			$multipage .= ($to < $pages ? '<a href="'.$this->_options['url'].$pages.'" class="last">... '.$realpages.'</a>' : '').
				($curpage < $pages ? '<a href="'.$this->_options['url'].($curpage + 1).'" class="next">'.$this->_options['next'].'</a>' : '').
				($this->_options['showkbd'] && $pages > $this->_options['page'] ? '<kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.$this->_options['url'].'\'+this.value; return false;}" /></kbd>' : '');

			$multipage = $multipage ? '<div class="qp_pages">'.($this->_options['shownum'] ? '<em>&nbsp;'.$this->_options['sumcount'].'&nbsp;</em>' : '').$multipage.'</div>' : '';
		}
		return array(
			// 分页的HTML
			'html'=>$multipage,
			// 总页数
			'pagenum'=>$realpages,
			// 当前页
			'curpage'=>$curpage,
		);
	}

	/**
	 * 得到当前所是第几页
	 *
	 * @return int
	 */
	private function _getCurPage(){
		if($this->_urlMethod == 'standard' || false !== strpos($this->_options['url'], '?')){
			$pagenum = $this->_request->getGet($this->_options['tagname']);
		}else{
			$pagenum = $this->_request->getParam($this->_options['tagname']);
		}
		return max($pagenum,1);
	}

	/**
	 * 解析URL
	 *
	 * @return void
	 */
	private function _parseUrl(){
		// 得到 URL
		$url = $this->_options['url'] ? $this->_options['url'] : $this->_request->currentURL();
		// 分页的参数名
		$tagname = $this->_options['tagname'];
		// 如果是常规模式
		if($this->_urlMethod == 'standard' || false !== strpos($url, '?')){
			$pattern = '/[\&]*'.$tagname.'[=|\d]*/';
			$url = preg_replace($pattern,'',$url);
			$url .= strpos($url, '?') ? '&amp;' : '?';
			$url .= $tagname.'=';
		}else{
			// 纯PHPINFO或重写模式
			$pattern = '/'.$tagname.'[\/|\d]*/';
			$url = preg_replace($pattern,'',$url);
			// 如果最后不是字符 '/'
			if($url[strlen($url)-1] != '/'){
				$url .= '/';
			}
			// 如果没有指定控制器或动作的话则要加了，因为这里要加一个参数
			$requestUri = $this->_request->server('REQUEST_URI');
			// 此时 $requestUri 可能 '/' 或 '/index/' 或 '/index/test/'
			// 去掉最后的 '/' 才能判断正常
			$len = strlen($requestUri);
			if($requestUri[$len-1] == '/'){
				$requestUri = substr($requestUri, 0, $len-1);
			}
			// 判断 '/' 的个数
			$sc = substr_count($requestUri, '/');
			if($sc < 1){
				$url .= $this->_request->getParam('controller').'/'.$this->_request->getParam('action').'/';
			}elseif($sc == 1){
				$url .= $this->_request->getParam('action').'/';
			}

			$url .= $tagname.'/';
		}
		$this->_options['url'] = $url;
	}


}
