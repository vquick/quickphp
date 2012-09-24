<?php
/**
 * 该文件是显示调试信息的模板文件
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Debuginfo.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
?>

<!-- QuickPHP II Debug Info -->
<style type="text/css">
.tr_click{
	cursor:pointer;
	background-color:#FFCC00;
}
.qp_table{
	border: 1px solid #000000;
	font-size: 12px;
	margin-top:20px;
	background-color:#eeeeee;
}
th{
	color:#000000;
	background-color:#FFCC00;
	padding:10px 10px 10px 10px;
}
.tgreen{
	color:green;
}
.tblue{
	color:blue;
}
.tred{
	color:red;
}
</style>
<script type="text/javascript">
function qp_debug_swap_show(id)
{
	var obj = document.getElementById(id);
	var disp = obj.style.display=='none' ? '' : 'none';
	obj.style.display = disp;
}
</script>
<table width="100%" class="qp_table">
  <tr style="">
    <th colspan="2"><a href="http://www.vquickphp.com" target="_blank">QuickPHP(II) Ver <?php echo QP_Sys::VERSION?></a> 调试信息台</th>
  </tr>
  <tr>
    <td width="100" class="tgreen">运行时间:</td><td class="tred"><?php echo round($debugInfo['endTime']-$debugInfo['beginTime'], 6); ?> 秒</td>
  </tr>

<?php
$frontURL = QP_Request::getInstance()->frontUrl();
if($frontURL):?>
  <tr>
    <td class="tgreen">来源URL:</td><td class="tblue"><?php echo $frontURL ?></td>
  </tr>
<?php endif;?>

  <tr>
    <td class="tgreen">当前URL:</td><td class="tblue"><?php echo QP_Request::getInstance()->currentUrl() ?></td>
  </tr>
  <tr>
    <td class="tgreen">控制器:</td><td class="tblue"><?php echo $debugInfo['controller']?></td>
  </tr>
  <tr>
    <td class="tgreen">动作方法:</td><td class="tblue"><?php echo $debugInfo['action']?></td>
  </tr>
  <tr>
    <td class="tgreen">应用布局:</td><td class="tblue"><?php echo isset($debugInfo['layout']) ? $debugInfo['layout'] : '&nbsp;'?></td>
  </tr>
  <tr>
    <td class="tgreen">输出视图:</td><td class="tblue"><?php echo isset($debugInfo['view']) ? $debugInfo['view'] : '&nbsp;'?></td>
  </tr>
  <tr>
    <td class="tgreen">匹配路由:</td><td class="tblue"><?php echo isset($debugInfo['router']) ? '<pre>'.htmlspecialchars(print_r($debugInfo['router'],true)).'</pre>' : '&nbsp;'?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_sql')">
    <td colspan="2">[SQL]</td>
  </tr>
  <tr style="display:none" id="qp_tr_sql">
    <td colspan="2">
    <?php
    $dbDebugInfo = (array)QP_Registry_Registry::getInstance()->get('DB_DEBUG');
    foreach($dbDebugInfo as $row):?>
    	时间:<span class="tred"><?php echo round($row['execTime'],6)?></span> 累计:<span class="tred"><?php echo round($row['totalTime'],6)?></span> SQL:<span class="tblue"><?php echo $row['sql']?></span> <br/>
    <?php endforeach;?>
    </td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_param')">
    <td colspan="2">[URI PARAM]</td>
  </tr>
  <tr style="display:none" id="qp_tr_param">
    <td colspan="2"><?php $params = QP_Request::getInstance()->getParam();if($params) qp_sys::dump($params)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_get')">
    <td colspan="2">[GET]</td>
  </tr>
  <tr style="display:none" id="qp_tr_get">
    <td colspan="2"><?php if(isset($_GET) && $_GET) qp_sys::dump($_GET)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_post')">
    <td colspan="2">[POST]</td>
  </tr>
  <tr style="display:none" id="qp_tr_post">
    <td colspan="2"><?php if(isset($_POST) && $_POST) qp_sys::dump($_POST)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_cookie')">
    <td colspan="2">[COOKIE]</td>
  </tr>
  <tr style="display:none" id="qp_tr_cookie">
    <td colspan="2"><?php if(isset($_COOKIE) && $_COOKIE) qp_sys::dump($_COOKIE)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_session')">
    <td colspan="2">[SESSION]</td>
  </tr>
  <tr style="display:none" id="qp_tr_session">
    <td colspan="2"><?php if(isset($_SESSION) && $_SESSION) qp_sys::dump($_SESSION)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_files')">
    <td colspan="2">[FILES]</td>
  </tr>
  <tr style="display:none" id="qp_tr_files">
    <td colspan="2"><?php if(isset($_FILES) && $_FILES) qp_sys::dump($_FILES)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_server')">
    <td colspan="2">[SERVER]</td>
  </tr>
  <tr style="display:none" id="qp_tr_server">
    <td colspan="2"><?php qp_sys::dump($_SERVER)?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_viewvar')">
    <td colspan="2">[视图变量]</td>
  </tr>
  <tr style="display:none" id="qp_tr_viewvar">
    <td colspan="2">
    <?php
    // 处理数据为友好显示
    function debug_dataShow($val){
	if(is_array($val)){
		return array_map('debug_dataShow',$val);
	}elseif(is_object($val)){
		return get_class($val).' Object';
	}elseif (is_resource($val)){
		return get_resource_type($val);
	}else{
		return QP_Func_Func::toHtml($val);
	}
    }
    $viewDebugInfo = QP_Registry_Registry::getInstance()->get('VIEW_DEBUG');
    qp_sys::dump(array_map('debug_dataShow',$viewDebugInfo));
    ?>
    </td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_includepath')">
    <td colspan="2">[搜索路径]</td>
  </tr>
  <tr style="display:none" id="qp_tr_includepath">
    <td colspan="2"><?php qp_sys::dump(explode(PATH_SEPARATOR,get_include_path()))?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_includefile')">
    <td colspan="2">[引入文件]</td>
  </tr>
  <tr style="display:none" id="qp_tr_includefile">
    <td colspan="2"><?php qp_sys::dump(get_included_files())?></td>
  </tr>

  <tr class="tr_click" onclick="qp_debug_swap_show('qp_tr_extensions')">
    <td colspan="2">[安装的扩展]</td>
  </tr>
  <tr style="display:none" id="qp_tr_extensions">
    <td colspan="2"><?php qp_sys::dump(get_loaded_extensions())?></td>
  </tr>


</table>
