<?php

//系统注册类
/*
 * 注册appclass应用类库
 * 注册的方法为：在对应的数组中添加键值对，键名为类的名字，值名为类文件名，值名为空即表示类文件名与类名相同。
 */

$appclass = [
    'guest'        => 'acter',
    'employee'     => 'employee',
	'flow'         => 'public/class/flow',
	'DataTables'   => 'public/class/datatable',
	'DataGrid'     => 'public/class/datagrid',
    'accDataTable' => 'datatableex',
    'idcard',
    'agent',
    'attrib'    => 'public/class/attrib',
    'user'      => 'public/class/user',
	'group'     => 'public/class/group',
	//'message' => 'public/class/message',
	'menu'      => 'public/class/menu',
	'letter'    => 'public/class/letter',
	'PHPMailer' => 'letter',
	'sitemsg'   => 'public/class/sitemsg',
	'mongrid'   => 'public/class/mongrid',
	'userAuth'  => 'public/class/userAuth',
    'erpAuth'   => 'public/class/erpAuth',
	'invoice'   => 'public/class/invoice',
	'mongoLogs' => 'mongoLogs',
];

