### 概述
该项目可快速开发后台，并保持了良好的二次开发性能。

基础页示例：



### 搭建环境

通过composer进行安装。
```$xslt
composer create-project waiterphp/app
```
通过builder工具安装admin
```
builder admin.project
```
请先修改数据库的配置信息。


### 项目构建
以构建列表页为例，可通过builder构建：

```
builder admin.list record.simpleList --table user_table --model record
```
项目会以table名生成model，名称会被格式化为驼峰命名法。当你想自定义model的名称时，请使用model参数

可使用的构建组件有：

|组件|含义|可用参数|
|:--:|:--|:--|
|list|分页列表页||
|form|表单页||
|chart|统计图表||

### 主要概念

#### 域
后台通过域的概念去管理所有控制器。

#### 配置工具
每一个组件，都提供一个工具类，帮我们更友好的生成相关数据。

#### 布局功能
1. 多组件
2. 多组件大小比率和顺序
3. 

#### list功能
1. 显示列表
2. 支持分页
3. 支持公共功能
4. 支持单条功能
5. 支持搜索
6. 支持单数据快速编辑
7. 支持多选操作
8. 列表支持数据转化和局部隐藏

### 
