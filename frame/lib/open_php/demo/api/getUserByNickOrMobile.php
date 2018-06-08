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
    $open = new com\dttx\www\Open();
    $open->setFields( array (
        'nick_mobile' => 'jiangjun',
    ) );
    $result = $open->request( '/user/getUserInfoByMobileOrNick', NULL );
    //var_dump( $result );

    if ( !$result ) throw new Exception( '调用api失败' );

    $result = json_decode( $result, TRUE );
    if ( !$result ) throw new Exception( '接口返回数据不是json格式' );

    dump( $result );
} catch (Exception $e) {
    echo '操作出错。错误信息：', $e->getMessage();
}