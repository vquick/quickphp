                 [QuickPHP 简介]

    QuickPHP 是新一代极速，强大，全面的PHP系统开源开发框架，它结合业界各著名框架的优点，
以具体项目经验为基础开发而成，适合各种层次的系统及各种水平的开发者应用。

　　QuickPHP 结合了 Zend,Kohana 等框架的优点，除了支持通常的MVC基本操作，Rewrite外，
还支持 Layout 布局开发(CI,Kohana都不支持)；支持 PHP CLI 运行模式，
如 "php index.php controller=index action=test id=10 name=vg”，这对于开发Crontab等系统
应用非常方便(CI,ZF都不支持)

　　以下是QuickPHP的一些显示改进点：

　　[方便灵活]

　　QuickPHP有非常方便查错的调试模式，尽可能的让开发者对出错信息一目了然；QuickPHP已为开发者
将产品发布后的PHP错误处理，无效访问，框架异常处理等问题完全处理妥当，您要做的只是把“debug”关闭即可；
QuickPHP自动装载机制非常的强大，无需任何设置即可实现控制器，模型间的继承，自定义组件的扩展，你要做的
只是放好文件后"new"即可。

　　[轻松使用Zend Framework组件]

　　如果您觉得QuickPHP自带的组件太少或不好用的话，ZF是QuickPHP强大的后盾，您只在QuickPHP中
 QP_Sys::zend('<ZF框架的路径>') 就可以完美的使用ZF的强大组件了。

　　[QuickPHP与Zend Framework对比的改良]
A：避免了繁琐的系统初始化工作，因为框架已帮你很好的解决了。
B：改良了配置文件及读取，采用Kohana框架的配置方式，读取上比Kohana框架更加方便。
C：简化了Layout的使用，使用操作更方便。
D：改良了视图的使用，如：增加了Kohana框架中的全局绑定功能，设置视图路径时兼容相对路径和绝对路径的使用，
视图文件或任意设置(作者比较喜欢用 .html 的扩展名来表示视图，当然你也可以使用ZF的 .phtml扩展名)。
E：改良了URL模式的支持，可以灵活的支持REWRITE，PATHINFO，STANDARD(普通url查询)；并且支持任意模式的URL地址生成。
F：改良了路由的使用，采用了Kohana框架中友好方式定义路由。


除了在开发便利性方面，QuickPHP还以高性能显著而著称，以下是QuickPHP相对其它框架的一个性能对比参考图。

　　
以下是在同一台机器上用：ab -n 1000 -c 200 "url" 进行的测试结果，都是只纯解析Layout+View的输出"Hello Word"。
以下具体数字，与机器性能有关,公供参考。

框架名称        | 版本  | 每秒并发数 | 平均最小时间(ms) | Layout支持
Cakephp        | 1.3.4 | 51.85      | 19.286          | 框架支持
Zend Framework | 1.11  | 60.52      | 16.523          | 框架支持
Kohana         | 3.0   | 150.01     | 6.666           | 自定义扩展
CodeIgniter    | 2.0   | 196.03     | 5.101           | 自定义扩展
ThinkPHP       | 3.0   | 285.75     | 3.500           | 自定义扩展
QuickPHP       | 2.5   | 530.96     | 1.883           | 框架支持


官方: http://www.vquickphp.com
