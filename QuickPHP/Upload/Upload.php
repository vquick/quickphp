<?php
/**
 * 文件上传 工具
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Upload.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/*
[示例如下]
// 生成对象
$up = new QP_Upload_Upload('file1');
if(!$up->hasUpload()){
	echo '没有文件上传';
	return;
}

// 设置属性
$up->set(array('savePath'=>'./img/'))->set(array('isThumb'=>true,'thumb'=>array(array(50,50),array(100,100))))->upload();
if($up->hasError()){
	echo $up->error();
}else{
	echo '上传的文件名：'.$up->getUploadFile().'<br/>';
	echo '二个缩略图文件名:';
	QP_Sys::dump($up->getThumbFile());
}
---------------------------------------------------------

[注意]

1:该工具暂不支持 FILE域为数组的方式上传,如下
 <input type="file" name="pfile[]" />
 <input type="file" name="pfile[]" />

2:别忘记 form 标记中指定 enctype="multipart/form-data"
*/
class QP_Upload_Upload
{
	/**
	 * 文件类型
	 *
	 * @var array
	 */
	private $_fileType = array
	(
		'other'=>array('application/octet-stream'),
		'bmp'=>array('image/bmp'),
		'gif'=>array('image/gif'),
		'txt'=>array('text/plain'),
		'jpg'=>array('image/jpg','image/jpe','image/jpeg','image/pjpeg'),
		'jpeg'=>array('image/jpg','image/jpe','image/jpeg','image/pjpeg'),
		'png'=>array('image/x-png','image/png'),
		'swf'=>array('application/x-shockwave-flash'),
	);


	/**
	 * 属性设置
	 *
	 * @var array
	 */
	private $_sets = array
	(
		// 可上传文件的类型,如果想上传更安全的文件(防止修改文件扩展名)； 如果想上传所有文件请设置为 "*"
		'type'=>'jpg|swf|gif|png|bmp',
		// 允许的最大文件大小,单位:KB  0:表示任何大小
		'maxSize'=>0,
		// 上传文件后保存的目录(要可写)
		'savePath'=>'',
		// 当目标文件存在时是否删除
		'isRemove'=>false,
		// 是否为生成缩略图, 注意: 缩略图都是 JPG 类型的
		'isThumb'=>false,
		/**
		 * 生成缩略图的尺寸,支持多组尺寸(即同时生成多张缩略图)定义.格式如下:
		 *
		 * array(array(50,40), array(100,80));
		 *
		 * 说明:
		 *
		 * 1:生成二组缩略图
		 * 第一组尺寸是: 50:缩略图最大宽度 40:缩略图最大高度
		 * 第二组尺寸是: 100:缩略图最大宽度 80:缩略图最大高度
		 *
		 * 2:生成的缩略图文件名
		 * 格式为: "[上传后的文件名(包含扩展名)].[缩略图宽]_[缩略图高].jpg]"
		 * 例如: 文件上传后的文件名为:"xxxx.jpg" 则第一组尺寸生成后的缩略图文件名为:"xxxxxx.jpg.50_40.jpg"
		 */
		'thumb'=>array(),
	);

	/**
	 * 属性信息
	 *
	 * @var array
	 */
	private $_options = array
	(
		// 错误序号
		'errorNo'=>0,
		// 错误定义
		'errorMsg'=>array(
			-1=>'没有指定FILE表单域',
			-2=>'没有文件上传',
			-3=>'文件超过大小',
			-4=>'不是指定的类型',
			-5=>'目标文件已存在',
			-6=>'目标文件不能写入',
		),
		// 生成的缩略图文件名
		'thumbFiles'=>array(),
		// FILE控件名称
		'inputName'=>'',
		// 上传文件后保存的文件名
		'fileName'=>'',
	);

	/**
	* 构造函数
	*
	* @param string $inputName: 表单中FILE控件名称,如 <input type="file" name="upfile"> 中的"upfile"
	*/
	public function __construct($inputName='')
	{
		$this->_options['inputName'] = $inputName;
	}

	/**
	* 判断当前表单是否有文件上传了
	*
	* @return bool
	*/
	public function hasUpload()
	{
		return isset($_FILES[$this->_options['inputName']]) && $_FILES[$this->_options['inputName']]['name'] != '';
	}

	/**
	* 设置 FILE控件名称
	*
	* @param string $inputName:FILE域名称
	*/

	public function setInputName($inputName)
	{
		$this->_options['inputName'] = $inputName;
		return $this;
	}

	/**
	* 设置上传属性
	*
	* @param array $sets:属性设置
	*/
	public function set($sets)
	{
		$this->_sets = array_merge($this->_sets,$sets);
		return $this;
	}

	/**
	* 上传文件动作
	*
	* 说明:$dstFileName 有以下三种处理情况
	*
	* 1:为空时,系统自动生成随机唯一的文件名;其扩展名与上传时所选择的文件一致.
	* 2:没有指定扩展名时如 "fileName".则系统自动加上上传时所选择文件的扩展名
	* 3:指定了完整的文件名如 "fileName.jpg" 则完全采用自定义的文件名
	*
	* @param string $dstFileName 目标文件名
	* @return bool
	*/
	public function upload($dstFileName='')
	{
		// 如果文件域不存在
		if ($this->_options['inputName']=='' || !isset($_FILES[$this->_options['inputName']]))
		{
			$this->_options['errorNo'] = -1;
			return false;
		}

		// 生成目标文件文件
		$this->_makeFileName($dstFileName);

		// 是否为可上传的文件
		$tmpFile = $_FILES[$this->_options['inputName']]['tmp_name'];
		if ($tmpFile =='' || !is_uploaded_file($tmpFile))
		{
			$this->_options['errorNo'] = -2;
			return false;
		}

		// 文件大小是否超过了设置
		if ($this->_sets['maxSize'] > 0)
		{
			if ($_FILES[$this->_options['inputName']]['size'] > ($this->_sets['maxSize']*1024))
			{
				$this->_options['errorNo'] = -3;
				return false;
			}
		}

		// 文件是否是指定的类型
		if ($this->_sets['type'] != '*')
		{
			// 得到所有允许的类型
			$typeArr = array();
			$ftypes = explode('|',$this->_sets['type']);
			foreach ($ftypes as $ft)
			{
				$typeArr = array_merge($typeArr, $this->_fileType[$ft]);
			}
			// 是否存在
			$ut = trim($_FILES[$this->_options['inputName']]['type']);
			if (!in_array($ut,$typeArr))
			{
				$this->_options['errorNo'] = -4;
				return false;
			}
		}

		// 目标文件是否存在
		$dstfile = $this->_sets['savePath'].$this->_options['fileName'];
		if (file_exists($dstfile))
		{
			if ($this->_sets['isRemove'])
			{
				@unlink($dstfile);
			}else{
				$this->_options['errorNo'] = -5;
				return false;
			}
		}

		// 复制文件
		if(@copy($tmpFile,$dstfile))
		{
			@chmod($dstfile,0777);
		}else{
			$this->_options['errorNo'] = -6;
			return false;
		}

		// 如果生成缩略图
		if($this->_sets['isThumb'])
		{
			$this->_makethumb();
		}
		return true;
	}

	/**
	* 得到上传后的文件名
	*
	* @param bool $path: true:包括全路径 false:不带路径
	* @return string
	*/
	public function getUploadFile($path=false)
	{
		// 如果有错误
		if($this->hasError()){
			return false;
		}
		return $path ? $this->_sets['savePath'].$this->_options['fileName'] : $this->_options['fileName'];
	}

	/**
	* 得到所有缩略图的文件名
	*
	* @return array
	*/
	public function getThumbFile()
	{
		return $this->_options['thumbFiles'];
	}

	/**
	* 判断是否出错了
	*/
	public function hasError()
	{
		return $this->_options['errorNo'] != 0;
	}

	/**
	* 返回错误信息
	*
	* @return string
	*/
	public function error()
	{
		return $this->hasError() ? $this->_options['errorMsg'][$this->_options['errorNo']] : '';
	}

	/**
	* 生成文件名
	*/
	private function _makeFileName($dstFileName)
	{
		if ($dstFileName == '')
		{
			$this->_options['fileName'] = $this->_randFileName().$this->_fileExt($_FILES[$this->_options['inputName']]['name']);
		}else{
			if (!strpos($dstFileName,'.')) $this->_options['fileName'] = $dstFileName.$this->_fileExt($_FILES[$this->_options['inputName']]['name']);
			else $this->_options['fileName'] = $dstFileName;
		}
	}

	/**
	* 得到随机唯一的文件名
	*/
	private function _randFileName()
	{
		return md5(uniqid(time().rand()));
	}

	/**
	* 得到小写的文件扩展名,如".php"
	*
	* @param string $fileName:文件名
	* @return string
	*/
	private function _fileExt($fileName)
	{
		return strtolower(strrchr($fileName,"."));
	}

	/**
	* 生成缩略图
	*
	*/
	private function _makethumb()
	{
		$img = $this->getUploadFile();
		$srcfile = $this->_sets['savePath'].$img;
		foreach ($this->_sets['thumb'] as $row)
		{
			@list($thumbWidth, $thumbHeight) = $row;
			$basefile = $img.'.'.$thumbWidth.'_'.$thumbHeight.'.jpg';
			$dstfile = $this->_sets['savePath'].$basefile;
			if (QP_Image_Image::thumbImg($srcfile,$dstfile,$thumbWidth,$thumbHeight)){
				$this->_options['thumbFiles'][] = $basefile;
			}
		}
	}

}
