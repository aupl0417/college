<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页 - 我的网站</title>
</head>
<body>
<h1>我是首页</h1>

<p>
    <?php if ( isset( $_COOKIE[ 'loginToken' ] ) ): ?>
        欢迎您，<?php echo $_COOKIE[ 'loginToken' ] ?>，<a href="logout.php">退出</a>
    <?php else: ?>
        您好，请<a href="login.php" target="_self">登录</a>
    <?php endif; ?>
</p>
</body>
</html>
