<?php
/**
 * UCenter 应用程序开发 Example
 *
 */

include dirname(__FILE__)."/config.inc.php";

include dirname(__FILE__)."/uc_client/client.php";

//注册用户
function  bbs_user_register( $username , $userpwd , $email ){

	$uid = uc_user_register(  $username, $userpwd , $email );
	/*if($uid <= 0) {
		if($uid == -1) {
			return '用户名不合法';
		} elseif($uid == -2) {
			return '包含要允许注册的词语';
		} elseif($uid == -3) {
			return '用户名已经存在';
		} elseif($uid == -4) {
			return 'Email 格式有误';
		} elseif($uid == -5) {
			return 'Email 不允许注册';
		} elseif($uid == -6) {
			return '该 Email 已经被注册';
		} else {
			return '未定义';
		}
	} else {
		return '注册成功';
	}*/
}

//登陆并同步用户状态
function  bbs_user_login( $username , $userpwd  ){
	list($uid, $username, $password, $email) = uc_user_login($username, $userpwd);
	//登陆的cookie
	setcookie('Example_auth', '', -86400);
	if($uid > 0) {
		//用户登陆成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
		setcookie('Example_auth', uc_authcode($uid."\t".$username, 'ENCODE'));
		//登陆并输出同步信号
		$ucsynlogin = uc_user_synlogin($uid);
		return $ucsynlogin;
		//exit;
	} elseif($uid == -1) {
		//return '用户不存在,或者被删除';
	} elseif($uid == -2) {
		//return '密码错';
	} else {
		//return '未定义';
	}
}

//同步退出
function  bbs_user_logout(  ){
	setcookie('Example_auth', '', -86400);
	//生成同步登录的代码
	$ucsynlogout = uc_user_synlogout();
	return $ucsynlogout;
}

//激活在bbs中的账号
function bbs_user_Activation( ){
	
	$dbserver = UC_DBHOST; //此处改成数据库服务器地址
	$dbuser =UC_DBUSER;//此处写数据库用户名
	$dbpwd =UC_DBPW;//数据库密码
	$dbname =UC_DBNAME;//数据库名称
	$charset ='utf8';//此处写字符集gbk或者utf8
	$uc_pre ='pre_ucenter_';//UC表前缀
	$dx_pre ='pre_';//Discuz! X2表前缀
	//此行开始向下不要改动
	set_time_limit(0); //0为无限制
	$connect=mysql_connect($dbserver,$dbuser,$dbpwd) or die("无法连接数据库");
	@mysql_select_db($dbname,$connect);
	mysql_query("set names $charset");
	$query = mysql_query("SELECT * FROM `{$uc_pre}members`  WHERE  `uid` not in(select `uid` from `{$dx_pre}common_member`) ",$connect);
	while($user = mysql_fetch_array($query)) {
		$password=$user[password];
		mysql_query(" replace INTO  `{$dx_pre}common_member` (uid,username,password,adminid,groupid,regdate,email) VALUES ('$user[uid]', '$user[username]', '$password','0','10','$user[regdate]','$user[email]') ");
		mysql_query(" replace INTO  `{$dx_pre}common_member_field_forum` (uid) VALUES ('$user[uid]')");
		mysql_query(" replace INTO  `{$dx_pre}common_member_field_home` (uid) VALUES ('$user[uid]')");
		mysql_query(" replace INTO  `{$dx_pre}common_member_count` (uid) VALUES ('$user[uid]')");
		mysql_query(" replace INTO  `{$dx_pre}common_member_profile` (uid) VALUES ('$user[uid]')");
		mysql_query(" replace INTO  `{$dx_pre}common_member_status` (uid) VALUES ('$user[uid]')");
	}
	return true;
}


//echo bbs_user_register('test_123_456','&*&\^&$%&jgf','test_123_456@qq.com');
//bbs_user_Activation();
//echo bbs_user_login('E0000002','&*&\^&$%&jgf');

//die('ok');
//echo bbs_user_login('admin','admin');
/*
//echo bbs_user_register('测试个测试','123123','xiaobai17@qq.com');
echo bbs_user_login('测试个测试','123123');
//echo "<script>window.location='http://127.0.0.1:8080/bbs/forum.php'</script>";
//echo bbs_user_logout();
bbs_user_Activation();
//include dirname(__FILE__)."/api/uc.php";

//uc = new uc_note();
//$uc->synlogout();
//print_r( _authcode() );

print_r( $_COOKIE['Example_auth'] );
print_r( 'ok' );
*/
?>