waitphpadmin是一套后台的快速开发方案。它通过build方式为你生成基础的代码，以实现一些常用常用功能（如列表的展示、搜索、排序）。方案也并提供了一些比较方便的方式，让你通过简单的修改便可以调整具体的细节。当然，方案很重要的优点，就是良好的二次开发性能。实际上，在该方案基础上写代码，和你在框架上进行开发，没有什么区别。

方案需要5.4以上的php环境，需要composer去安装相关包。如无相关程序，请查阅相关文档安装。

#### 生成项目

 以'admin'构建项目为例，执行以下命令便可以构建项目：

```bash
composer create-project waiterphp/app admin
cd admin
chmod 777 storage -R
php build admin
```
 
现在，你就可以访问项目下的public/index.html来查看后台了。默认的登录帐号密码都为admin

#### 生成列表
生成页面前，请先编辑config/database.php文件，配置数据库信息。

以常见的列表页为例，我们如果想把表'product_info'做列表展示，只需要执行以下命令：
```bash
php build admin.list --table product_info (--path product  --controller controller_name --model model_name)
```
命令会自动生成’“-table”参数对应的模型文件和控制器文件，在构建文件名时，命令会自动把下划线命名转化为驼峰命名。控制器的文件名也会自动添加页面功能标识。（比如此处都会添加Table后缀）
对于多级文件目录，我们可以设置--path去指定父目录名。当父目录名是表名的一部分时，命令会认为父目录是表前缀，自动从文件名中去除。
如--path设置为product，就会生成Product的父目录，文件夹下InfoTable的类文件。
当然，你不希望文件名和表名一样，可以直接设置--controller和--model设置父目录下的类文件名称。
注意：所有的文件名都为驼峰命名，命令会做自动转化。

此时，我们便可以通过/Product/InfoTable访问该列表页了。

> 当然，你会发现系统并没有自动为你生成菜单，请编辑config/menu.php把你刚刚生成的控制器添加到菜单中。

#### 表单和树
方案还提供了一些其他的页面小组件。
如表单页：
``` bash
php build admin.form --table product_info (--path product  --controller controller_name --model model_name)
```
树状分类：
``` bash
php build admin.tree --table product_info (--path product  --controller controller_name --model model_name)
```

因为以上代码公用一个info模型（都处理product_info表），所以运行时会询问你是否要覆盖dao文件，请选择否。而它们生成的控制器是不会冲突的，因为生成文件名都加了功能对应的后缀，如InfoForm  、InfoTree。

#### 控制器接口

TableConfig接口
| 接口      |    含义 | 实例|
| :-------- | --------| :-- |
| setShowFields  | 设置显示字段（默认全部显示） |  setShowFields('username,sex,birthday')   |
| setOrderFields     |   设置排序字段（默认为所有number类型的字段） |  setOrderFields('username,birthday')  |
| addTableAction      |    设置全表操作 | addTableAction('actionName')->setName('按钮名')->setAjax()->setUrl('contoller_method')|
|addRowAction|设置行操作|addRowAction('actionName')->setName('按钮名')->setAjax()->setUrl('contoller_method?@primaryKey@=@data.id@')|
|setFastAdd|设置快速添加|setFastAdd('field_name')|
|setFastEdit|设置字段为快速编辑|setFastEdit('field_name')|
|setSearch|设置搜索项（type有 = 等号， like 文字，range 时间区间）|setSearch('field', ''searchType') |
|setDetail|设置详情页链接|setDetail('current_controller_method')|


FormConfig接口

TreeConfig接口
