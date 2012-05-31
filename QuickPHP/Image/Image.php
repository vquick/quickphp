<?php
/**
 * 图像 工具
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Image.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/*
[示例1:图片验证码的使用]

---------- reg.html的代码如下 ---------------
<form method=post action="/reg/register">
	<img src="/reg/code" onclick="this.src='/reg/code" />
	输入图片中的字符,不区分大小写,看不清请点击图片更换:
	<input type="text" name="code">
	<input type="submit" value="提交">
</form>

---------- reg.php 控制器的代码如下 ---------
public function register()
{
	echo QP_Image_Image::chkVerify() ? '验证码正确' : '验证码错误';
}
public funciton code()
{
	QP_Image_Image::imgVerify();
}
------------------------------------------

[示例:2图片处理]
------------------------------------------
// 输出图片的信息
print_r(QP_Image_Image::getInfo('/home/a.jpg'));
// 生成 a.jpg 的缩略图,宽度:100 高度:120
QP_Image_Image::thumbImg('a.jpg','a-thumb.jpg',100,120);

[注意]
1:在 Uploadfile 文件上传工具中已经包括了该缩略图方法的调用,详情请看文件上传工具的使用.

*/
class QP_Image_Image
{
	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	* 得到图片的信息
	*
	* @param $imgFile:文件文件名
	*/
	static public function getInfo($imgFile)
	{
		$imageInfo = getimagesize($imgFile);
		if( $imageInfo!== false){
			$imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
			$imageSize = filesize($imgFile);
			$info = array(
				"width"=>$imageInfo[0],
				"height"=>$imageInfo[1],
				"type"=>$imageType,
				"size"=>$imageSize,
				"mime"=>$imageInfo['mime']
			);
			return $info;
		}else{
			return false;
		}
	}

	/**
	* 生成缩略图
	*
	* 缩略图会根据源图的比例进行缩略的，生成的缩略图格式是 JPG
	*
	* @param $srcfile:源文件名
	* @param $dstfile:生成缩略图的文件名,扩展名必需为 ".jpg"
	* @param $thumbWidth:缩略图最大宽度
	* @param $thumbHeight:缩略图最大高度
	*/
	static public function thumbImg($srcfile,$dstfile,$thumbWidth,$thumbHeight)
	{
		// 缩略图大小
		$tow = $thumbWidth;
		$toh = $thumbHeight;

		$make_max = 0;
		$maxtow = $thumbWidth;
		$maxtoh = $thumbHeight;
		if($maxtow >= 300 && $maxtoh >= 300) {
			$make_max = 1;
		}

		// 获取图片信息
		$im = '';
		if($data = getimagesize($srcfile)) {
			if($data[2] == 1) {
				$make_max = 0;//gif不处理
				if(function_exists("imagecreatefromgif")) {
					$im = imagecreatefromgif($srcfile);
				}
			} elseif($data[2] == 2) {
				if(function_exists("imagecreatefromjpeg")) {
					$im = imagecreatefromjpeg($srcfile);
				}
			} elseif($data[2] == 3) {
			if(function_exists("imagecreatefrompng")) {
				$im = imagecreatefrompng($srcfile);
			}
			}
		}
		if(!$im) return '';

		$srcw = imagesx($im);
		$srch = imagesy($im);

		$towh = $tow/$toh;
		$srcwh = $srcw/$srch;
		if($towh <= $srcwh){
			$ftow = $tow;
			$ftoh = $ftow*($srch/$srcw);

			$fmaxtow = $maxtow;
			$fmaxtoh = $fmaxtow*($srch/$srcw);
		} else {
			$ftoh = $toh;
			$ftow = $ftoh*($srcw/$srch);

			$fmaxtoh = $maxtoh;
			$fmaxtow = $fmaxtoh*($srcw/$srch);
		}
		if($srcw <= $maxtow && $srch <= $maxtoh) {
			$make_max = 0;// 不处理
		}
		if($srcw > $tow || $srch > $toh) {
			if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$ni = imagecreatetruecolor($ftow, $ftoh)) {
				imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
				// 大图片
				if($make_max && @$maxni = imagecreatetruecolor($fmaxtow, $fmaxtoh)) {
					imagecopyresampled($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
				}
			} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && @$ni = imagecreate($ftow, $ftoh)) {
				imagecopyresized($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
				// 大图片
				if($make_max && @$maxni = imagecreate($fmaxtow, $fmaxtoh)) {
					imagecopyresized($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
				}
			} else {
				return '';
			}
			if(function_exists('imagejpeg')) {
				imagejpeg($ni, $dstfile);
				// 大图片
				if($make_max) {
					imagejpeg($maxni, $srcfile);
				}
			} elseif(function_exists('imagepng')) {
				imagepng($ni, $dstfile);
				// 大图片
				if($make_max) {
					imagepng($maxni, $srcfile);
				}
			}
			imagedestroy($ni);
			if($make_max) {
				imagedestroy($maxni);
			}
		}
		imagedestroy($im);
		return file_exists($dstfile);
	}

	/**
	* 生成图片验证码
	*
	* @param int $length :验证码长度
	* @param int $mode :模型 0:数字 1:小写字母 2:大写字母 3:字母与数字组合
	* @param string $type :指定图片类型，一般用默认值.
	* @param int $width :图片宽
	* @param int $height :图片高
	*/
	static public function imgVerify($length=4,$mode=3,$type='png',$width=48,$height=22)
	{
		// 生成随机数保存在 SESSION 中
		$randval = QP_Func_Func::randString($length,$mode);
		QP_Session_Session::set('QP_Verify_Code',md5(strtolower($randval)));

		$width = ($length*9+10)>$width?$length*9+10:$width;
		if ( $type!='gif' && function_exists('imagecreatetruecolor')) {
			$im = @imagecreatetruecolor($width,$height);
		}else {
			$im = @imagecreate($width,$height);
		}
		$r = Array(225,255,255,223);
		$g = Array(225,236,237,255);
		$b = Array(225,236,166,125);
		$key = mt_rand(0,3);

		//$backColor = imagecolorallocate($im, $r[$key],$g[$key],$b[$key]);    //背景色（随机）
		$backColor = imagecolorallocate($im, 255,255,255);    //背景白色
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
		//$pointColor = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));                 //点颜色
		$pointColor = imagecolorallocate($im,255,255,255);

		@imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
		@imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
		$stringColor = imagecolorallocate($im,mt_rand(0,200),mt_rand(0,120),mt_rand(0,120));
		// 干扰
		for($i=0;$i<10;$i++){
			//$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			//imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
		}

		for($i=0;$i<25;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
		}

		@imagestring($im, 5, 5, 3, $randval, $stringColor);

		header("Content-type: image/".$type);
		$ImageFun='Image'.$type;
		$ImageFun($im);
		imagedestroy($im);
	}

	/**
	* 检测输入的验证码是否正确
	*/
	static public function chkVerify($verifyCode)
	{
		return QP_Session_Session::get('QP_Verify_Code') == md5(strtolower($verifyCode));
	}

}
