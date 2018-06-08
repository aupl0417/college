<?php

//系统注册类
/*
 * 注册appclass应用类库
 * 注册的方法为：在对应的数组中添加键值对，键名为类的名字，值名为类文件名，值名为空即表示类文件名与类名相同。
 */

$appclass = [ 
    'guest'      => 'acter',
    'attrib'     => 'public/class/attrib',
    'user'       => 'public/class/user',
    'userAuth'   => 'public/class/userAuth',
    'account'    => 'public/class/account',
	'group'      => 'public/class/group',
	'message'    => 'public/class/message',
	'menu'       => 'public/class/menu',
	'letter'     => 'public/class/letter',
	'PHPMailer'  => 'letter',
    'DataTables' => 'public/class/datatable',
    'DataGrid' => 'public/class/datagrid',
];

