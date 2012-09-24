<?php
/**
 * 当框架的 自动装载器失败 并且 当 APP处于调试状态 时会自动输出这个信息模板
 *
 * @category QuickPHP(II)
 * @copyright http://www.vquickphp.com
 * @version $Id: Loaderror.php 1236 2011-10-23 08:52:02Z yuanwei $
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Quickphp Framework APP错误</title>
</head>
<body>
  <h3>自动装载器错误</h3>
  <p>
      <b>Message:</b>

      <!-- 文件找不到的提示 -->
      <?php if(isset($fileError)):?>
      找不到类: <span style="color:red;"><b>
      <?php 
		/* 如果是控制器则让输出更友好容易看出错误 */
		if(strtolower(substr($class,-10)) == 'controller'){
			$class = strtolower($class);
			$class = ucfirst(str_replace('controller','Controller',$class));
		}
		echo $class;
      ?>
      </b></span>
      所对应的文件: <span style="color:green;">
      <?php
		// 根据类得到对应的文件名
		$classFile = $spr = '';
		$dirArr = array_map('ucfirst',explode('_',$class));
		foreach ($dirArr as $k=>$path){
			$classFile .= $spr.$path;
			$spr = '/';
		}
		echo $classFile.'.php';
      ?>
      </span> <br/><br/>
      [提示]：请检查以下目录中是否有该文件的定义(如果是Linux/Unix系统请注意文件名的大小写):
      <hr/>
      <?php QP_Sys::dump(explode(PATH_SEPARATOR,get_include_path())); ?>
      <hr/>
      <?php endif;?>

      <!-- 类找不到的提示 -->
      <?php if(isset($classError)):?>
      在文件：<span style="color:green;"><?php echo $includeFile?></span> 中找不到类：<span style="color:red;"><b><?php echo $class ?></b></span> 的定义 <br/><br/>
      [提示]：请检查引用的类名是否和定义的类名一至!
      <?php endif;?>
  </p>

  <h3>Stack Trace:</h3>
  <pre><?php debug_print_backtrace(); ?></pre>

</body>
</html>
