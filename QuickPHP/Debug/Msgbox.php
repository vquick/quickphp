<?php
/*
 * 默认的提示消息框
 * 
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Msgbox.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- <meta http-equiv="refresh" content="5;url=http://163.com"> -->
<title>Message</title>
<script type="text/javascript">
	// 页面跳转
	var url = "<?php echo $url?>";
	function goToPage()
	{
		if (url == '') history.back();
		else if (url == 'close') window.close();
		else location.href = url;
	}
	
	// 倒计时时间
	var time = <?php echo $time?>;
	function autoTimeShow()
	{
		--time;
		if (time < 0)
		{
			goToPage();
			return;
		}	   
		document.getElementById('showTime').innerHTML = time;
		setTimeout(autoTimeShow,1000); 
	}
</script> 
<style type="text/css">
body{
    width:100%;
	text-align: center;
	padding-top: 100px;
	font-size:14px;
}
#msgbox{
	border: 1px solid #cccccc;
	width: 500px;
	margin:0 auto;
}
#msgbox-title{
	background: #eeeeee;
	border-bottom: 1px solid #cccccc;
	padding-left: 4px;
	height: 25px;
	line-height: 25px;
	text-align: center;
	font-weight: bold;
	color: #ff0000;
	text-align: left;
}
#content{
 border-left: 4px solid #eeeeee;
 border-right: 4px solid #eeeeee;
 border-bottom: 4px solid #eeeeee;
 padding-top: 30px;
}
#msgbox-text{
  
}
#msgbox-button{
	margin-top: 20px;
	margin-bottom: 15px;
}
#showTime{
	color:green;
}
</style>
</head>
<body onload="autoTimeShow()">
 <div id="msgbox" align="center">
    <div id="msgbox-title"><span style="float:left;">提示消息</span><span style="float:right; color:#0000FF;"><span id="showTime"><?php echo $time?></span>&nbsp;</span></div>
	<div id="content">
    	<div id="msgbox-text"><?php echo $msg?></div>
    	<div id="msgbox-button"><button style="height:22px;" onclick="goToPage()" type="button">确定</button></div>
    </div>
 </div>
</body>
</html>
        