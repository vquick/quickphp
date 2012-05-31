<?php
/**
 * CURL 工具
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Date.php 1236 2011-10-23 08:52:02Z yuanwei $
 */

/**
[示例]
// 返回当前的日期如 "2008-10-12"
echo QP_Date_QP_Date_Date::getDate();
// 比较两个时间
echo QP_Date_Date::compareTiem('2006-10-12','2006-10-11');
// 日期加天数： "2005-9-25"+6 = "2005-10-01"
echo QP_Date_Date::dateAddDay("2005-10-20",6);
// 日期减天数： "2005-10-20"-10 = "2005-10-10"
echo QP_Date_Date::dateDecDay("2005-10-20",10);
// 日期减日期: "2005-10-20"-"2005-10-10"=10
echo QP_Date_Date::dateDiff('2005-10-20','2005-10-10');
// 时间相减
echo QP_Date_Date::timeDiff('2005-10-20 10:00:00','2005-10-20  08:00:00');
*/
class QP_Date_Date{

	/**
	 * 防止类实例化或被复制
	 *
	 */
	private function __construct(){}
	private function __clone(){}

	/**
	* 得到当前时间
	*
	* @param string $fmt :日期格式
	* @param int $time :时间，默认为当前时间
	* @return string
	*/
	static public function getDate($fmt='Y-m-d H:i:s',$time=null)
	{
		$times = $time ? $time : time();
		return date($fmt,$times);
	}

	/**
	* 计算日期天数差
	*
	* @param string $Date1 :如 "2005-10-20"
	* @param string $Date2 :如 "2005-10-10"
	* @return int
	* 例子:"2005-10-20"-"2005-10-10"=10
	*/
	static public function dateDiff($Date1,$Date2)
	{
		$DateList1 = explode("-",$Date1);
		$DateList2 = explode("-",$Date2);
		$d1 = mktime(0,0,0,$DateList1[1],$DateList1[2],$DateList1[0]);
		$d2 = mktime(0,0,0,$DateList2[1],$DateList2[2],$DateList2[0]);
		$Days = round(($d1-$d2)/3600/24);
		return $Days;
	}

	/**
	* 计算日期加天数后的日期
	*
	* @param string $date :如 "2005-10-20"
	* @param int $day  :如 6
	* @return string
	* 例子:2005-9-25"+6 = "2005-10-01"
	*/
	static public function dateAddDay($date,$day)
	{
		$daystr = "+$day day";
		$dateday = date("Y-m-d",strtotime($daystr,strtotime($date)));
		return $dateday;
	}

	/**
	* 计算日期加天数后的日期
	*
	* @param string $date :如 "2005-10-20"
	* @param int $day  :如 10
	* @return string
	* 例子:"2005-10-20"-10 = "2005-10-10'
	*/
	static public function dateDecDay($date,$day)
	{
		$daystr = "-$day day";
		$dateday = date("Y-m-d",strtotime($daystr,strtotime($date1)));
		return $dateday;
	}

	/**
	* 比较两个时间
	*
	* @param string $timeA :格式如 "2006-10-12" 或 "2006-10-12 12:30" 或 "2006-10-12 12:30:50"
	* @param string $timeB :同上
	* @return int   0:$timeA = $timeB
	*              -1:$timeA < $timeB
	*               1:$timeA > $timeB
	*/
	static public function compareTiem($timeA,$timeB)
	{
		$a = strtotime($timeA);
		$b = strtotime($timeB);
		if($a > $b)        return 1;
		else if($a == $b)  return 0;
		else               return -1;
	}

	/**
	* 计算时间a减去时间b的差值
	*
	* @param string $timeA :格式如 "2006-10-12" 或 "2006-10-12 12:30" 或 "2006-10-12 12:30:50"
	* @param string $timeB :同上
	* @return flat   实数的小时,如"2.3333333333333"小时
	*/
	static public function timeDiff($timeA,$timeB)
	{
		$a = strtotime($timeA);
		$b = strtotime($timeB);
		$c = $a-$b;
		$c = $c / 3600;
		return $c;
	}

}
