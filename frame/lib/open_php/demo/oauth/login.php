<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登录 - 我的网站</title>
</head>
<body>
<?php if ( isset( $_COOKIE[ 'loginToken' ] ) ): ?>
    您已登录，<a href="logout.php">退出</a>
<?php else: ?>
    用户名：<input type="text" name="uname" disabled="disabled"/><br/>
    密码：<input type="password" name="pwd" disabled="disabled"/><br/>
    <input type="button" value="登录"/><br/>
    <a href="#" target="_self">使用QQ登录</a>&nbsp;&nbsp;
    <a href="redirect.php" target="_self" title="使用云联惠账号登录"><img src="170-x-32.png"/></a>
<?php endif; ?>
</body>
</html>