### waiterphp后台项目方案
本项目用于快速开发后台程序。它提供了一些后台常用功能，如登录、权限、列表、表单、统计等。项目本身并不影响waiterphp/app的基本结构。所以，您可以很方便的进行二次开发。

项目的前端部分采用vue开发。有前端二次开发需求的可访问项目[waiterphp/admin-vue]()

### 搭建环境

通过composer进行安装。
```$xslt
composer create-project waiterphp/admin

```

修改数据库的配置文件。



### 项目构建
以构建列表页为例，可以采用builder的方式：

```
builder admin.list record.simpleList --table user_table --dao record
```

可使用的构建组件有：

|组件|含义|可用参数|
|:--:|:--|:--|
|list|分页列表页||
|form|表单页||
|chart|统计图表||

