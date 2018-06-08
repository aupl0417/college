<?php
header("Content-Type: text/html; charset=utf-8");

require '../../lib/inc.php';
require LIB_PATH . '/auth.class.php';
$auth = new com\dttx\www\Auth();
$token = $auth->getAccessToken();

if ( $token ) {
    //获取openID
    $openID = $auth->getOpenID( $token[ 'access_token' ] );
    if ( $openID ) {
        echo 'openid:', $openID;

        $user = getUserByOpenID( $openID );

        if ( !$user ) {
            //把accessToken 和 openID保存进数据库
            $user = createUserByOpenID( $openID, $token );
            if ( !$user ) {
                exit( '创建用户失败' );
            }
        }

        //保存登录状态
        setcookie( 'loginToken', $user[ 'nickName' ], 0, '/', $_SERVER['HTTP_HOST'] );
        //跳转到首页
        header( 'Location:index.php' );
    } else {
        echo '获取openID失败，请查看日志';
    }
} else {
    echo '获取access token失败，请查看日志';
}

//通过openID创建本网站的用户
function createUserByOpenID ($openID, $token) {
    //以下为示例代码，请结合您自己的网站程序，编写实际代码
    /*
     $success = $mysqli->query("INSERT INTO user (open_id, nick_name, token) VALUES ('$openID', 'user_$openID', '$token')");
     return $success ? array('id'=>$mysqli->insert_id, 'nickName'=>"user_$openID") : FALSE;
     */

    //以下代码为测试代码
    return array ('id' => '123', 'nickName' => "user_$openID");
}

//通过openID查找用户
function getUserByOpenID ($openID) {
    //以下为示例代码，请结合您自己的网站程序，编写实际代码
    //return $mysqli->query("SELECT id, open_id, nick_name, token FROM user WHERE openID = '$openID' LIMIT 1")->fetch_assoc();

    //以下代码为测试代码
    return FALSE;
}

//end of file callback.php