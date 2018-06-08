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
        'order_id' => $_POST['val'],
    ) );
    $result = $open->request( '/order/getById' );
    //dump( $result );

    if ( !$result ) throw new Exception( '调用api失败' );

    $result = json_decode( $result, TRUE );
    if ( !$result ) throw new Exception( '接口返回数据不是json格式' );

    //开发者不存在：partner_id=72rerg482dxyzf4r
    dump( $result );
} catch (Exception $e) {
    echo '操作出错！<br/>错误信息：', $e->getMessage();
}