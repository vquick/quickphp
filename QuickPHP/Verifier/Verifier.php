<?php
/**
 * 字符验证 工具
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Verifier.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/*
Email    => 是否为有效的Email地址
Numeric  => 是否为全是数字的字符串(可以是 "0" 开头的数字串)
QQ       => 腾讯QQ号
IdCard   => 身份证号码
China    => 是否为中文
Onchina  => 是否含有中文
Zip      => 邮政编码
Phone    => 固定电话(区号可有可无)
Mobile   => 手机号码
MobilePhone => 手机和固定电话
Url      => URL地址
Account  => 用户帐号(字母开头，由字母数字下划线组成，4-20字节)
ip       => IP地址
word     => 合法字符(字母，数字，下划线)
*/
class QP_Verifier_Verifier{
	
	//定义的正则表达式
	static private $_regExp = array(
		'email'       => '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([_a-z0-9]+\.)+[a-z]{2,5}$/',
		'numeric'     => '/^[0-9]+$/',
		'zip'         => '/^[1-9]\d{5}$/',
		'phone'       => '/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/',
		'mobile'      => '/^((\(\d{2,3}\))|(\d{3}\-))?13\d{9}$/',
		'mobilephone' => '/(^[0-9]{3,4}\-[0-9]{3,8}$)|(^[0-9]{3,12}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)/',
		'qq'          => '/^[1-9]*[1-9][0-9]*$/',
		'china'       => '/^[\x7f-\xff]+$/',
		'onchina'     => '/[\x7f-\xff]/',
		'idcard'      => '/(^\d{15}$)|(^\d{17}([0-9]|X)$)/',
		'url'         => '/[a-zA-Z]+:\/\/[^\s]*/',
		'account'     => '/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/',
		'ip'          => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',
		'word'        => '/[a-zA-Z0-9_]$/',
	);

	/**
	 * 检测字符串
	 *
	 * @param string $key :检测的类型，如:"email" 或者是自定义的正则表达式,如:"/^a/"
	 * @param string $string :要检测的字符串
	 * 
	 * @return boolean
	 */
	static public function valid($key,$string)
	{
		$key = strtolower($key);
		// 是定义的正则表达式
		if(array_key_exists($key,self::$_regExp)){
			return preg_match(self::$_regExp[$key],$string);
		}else{
			// 直接正则来判断
			return preg_match($key,$string);
		}
	}
}












