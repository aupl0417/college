<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/6
 * Time: 11:51
 */
header( "Content-Type: text/html; charset=utf-8" );
require( '../../lib/inc.php' );

try {
    /**
     * TODO:通过数据库获取已授权的access_token
     */
    $accessToken = '98c92723214bc95e9f0192c2ee1d61e2';

    $open = new com\dttx\www\Open();
    $open->setFields( array (
        'open_id' => $_POST[ 'open_id' ],
    ) );
    $result = $open->request( '/order/getByBuyerId', NULL, $accessToken );
    //var_dump( $result );

    if ( !$result ) throw new Exception( '调用api失败' );

    $result = json_decode( $result, TRUE );
    if ( !$result ) throw new Exception( '接口返回数据不是json格式' );

    dump( $result );
} catch (Exception $e) {
    echo '操作出错。错误信息：', $e->getMessage();
}