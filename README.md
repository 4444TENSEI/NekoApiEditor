部署前端
1.pnpm i
2.pnpm dev
3.pnpm build

部署后端
1.导入server/main.sql数据库
2.修改server/config.php的数据库连接信息

前端结构
1.页面整体写到了src/App.vue作为单页网站，使用provide进行跨组件共享函数到components/NekoFrame.vue这个框架中。
2.components/NekoFrame.vue:框架，包含顶栏侧边栏和操作按钮。

后端结构：
config.php：配置文件
set.php：接收油猴数据的接口文件
get.php：响应前端登录和查询和删除文件
db.php：数据库连接用的，不用管
