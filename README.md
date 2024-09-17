这款插件是基于translate翻译服务实现的WordPress网页自动翻译，只能实现前端翻译，如果要实现HTML源码翻译，可以联系官方使用他们的TCDN服务。

插件名称：小半网页翻译(WPTR)


功能：<br>

可以设置翻译按钮显示位置（网页顶部、底部、菜单、小工具）<br>
如果设置了小工具，需要到后台小工具去添加翻译按钮<br>
网页顶部和底部是直接显示语言切换按钮<br>
菜单和小工具是有一个Language按钮带Emoji符号，点击了才能出现语言切换框<br>
自定义语言（图标可以不设置）<br>
用的client.edge方式（就是基于微软翻译来实现），我没有把官方所有内容看完，稳定性、速度大概率会受微软翻译影响吧？<br>

默认改为本地调用translate.js文件，没有用官方的staticfile，可以自己把translate.js上传到对象存储加权限，改为远程调用.<br>
默认会根据用户客户端ip自动显示对应语言(有设置的前提下)，官方说准确率为96%。<br>


其他说明：

翻译按钮显示错乱或者按钮无反应，是因为和你的主题不兼容，最完美的兼容是联系你的主题作者内置.<br>


怎么把本地调用translate.js文件改成远程调用？

先把translate.js文件上传到你的对象存储里面。（或者看看大厂提供的静态资源库有没有这个）<br>
然后复制对应的translate.js文件链接。<br><br>
修改插件代码158行，把远程translate.js链接填写进来，删掉注释，然后给本地调用159的代码加上注释保存就行了。<br>
如果是用自己的对象存储，一定要加只能自己网站调用的权限以及禁止直接访问！<br>


常见语言简码：<br>

简体中文：chinese_simplified<br>
繁体中文：chinese_traditional<br>
英文：english<br>
韩语：korean<br>
日语：japanese<br>
法语：french<br>
德语：deutsch<br>
意大利语：italian<br>
