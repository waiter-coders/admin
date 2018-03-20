<?php
//分页
Menu::classify('record', '分页列表','user')
    ->item('simple','简单分页','show')
    ->item('Search','分页搜索','show')
    ->item('FastEdit','快速编辑','show')
    ->item('CheckBox','列表多选','show')
    ->item('BackgroudColor','特殊条纹背色','show')
    ->item('BatchAdd','批量添加','show');

// 表单
Menu::classify('Form', '表单操作','user')
    ->item('Editor','简单表单','show')
    ->item('Detail','简单详情','show')
    ->item('HtmlEditor','富文本编辑器','show')
    ->item('Image','图片相关','show')
    ->item('Upload','上传相关','show')
    ->item('Time','时间相关','show');

//树形数据
Menu::classify('classify', '多层分类','user')
    ->item('Simple','简单列表','show');

//统计相关
Menu::classify('statistics', '统计','user')
    ->item('LineChart','折线图','show');

//返回菜单
return Menu::allClassify();