<?php
/**
 * Class Auth
 *
 * @package com\dttx\www
 */

namespace com\dttx\www;

require LIB_PATH . '/log.class.php';

class Auth {

    //日志类
    protected $log = NULL;

    //配置
    protected $config = NULL;

    public function __construct () {
        //开启session
        if ( PHP_SESSION_ACTIVE != session_status() ) session_start();
        //实例化一个日志类
        $this->log = new Log();
        //获取配置
        $this->config = require( LIB_PATH . '/config.php' );
    }

    /**
     * 跳转到登录授权页
     *
     * @return NULL
     */
    public function login () {
        //生成client端的状态值，防止CSRF攻击
        $state = md5( uniqid( rand(), TRUE ) );
        $_SESSION[ 'state' ] = $state;

        //提交数据
        $data = array (
            'response_type' => 'code',
            'partner_id'    => $this->config[ 'base' ][ 'partner_id' ],
            'app_id'        => $this->config[ 'base' ][ 'app_id' ],
            'redirect_uri'  => $this->config[ $this->config[ 'base' ][ 'env' ] ][ 'oauth' ][ 'redirect_uri' ],
            'state'         => $state,
            'scope'         => $this->config[ 'base' ][ 'scope' ],
            'view'          => $this->config[ 'base' ][ 'view' ],
        );

        //合并url
        $url = $this->config[ $this->config[ 'base' ][ 'env' ] ][ 'oauth' ][ 'access_code_url' ] . '?' . http_build_query( $data );

        $this->log->write( '重定向到：' . $url );

        //301跳转
        header( 'HTTP/1.1 301 Moved Permanently' );
        header( "Location:$url" );
    }

    /**
     * 获取access token
     *
     * @return mixed 失败：FALSE，成功：数组
     * example of success:
     * array(
     * //access token
     * 'access_token'  =>  '3a93acc77fe5fcc3d17ff8e92e372940',
     * //刷新token
     * 'refresh_token' =>  'b07fd33006d87aedf710a08bb5e5b605',
     * //剩余有效时间
     * 'expire_in'     =>  '7776000'
     * )
     */
    public function getAccessToken () {
        if ( !isset( $_GET[ 'access_code' ] ) ) return $this->log->write( '获取access_code失败：返回参数缺少access_code' );
        if ( !isset( $_GET[ 'state' ] ) ) return $this->log->write( '获取access_code失败：返回参数缺少state' );
        if ( $_GET[ 'state' ] != $_SESSION[ 'state' ] ) return $this->log->write( '获取access_code失败：state不匹配' );

        //提交数据
        $data = array (
            'grant_type'   => 'authorization_code',
            'partner_id'   => $this->config[ 'base' ][ 'partner_id' ],
            'app_id'       => $this->config[ 'base' ][ 'app_id' ],
            'secret_key'   => $this->config[ 'base' ][ 'secret_key' ],
            'code'         => $_GET[ 'access_code' ],
            'redirect_uri' => $this->config[ $this->config[ 'base' ][ 'env' ] ][ 'oauth' ][ 'redirect_uri' ],
        );

        $url = $this->config[ $this->config[ 'base' ][ 'env' ] ][ 'oauth' ][ 'access_token_url' ] . '?' . http_build_query( $data );

        $this->log->write( '获取access token：' . $url );

        $response = Open::curlExec( $url );
        //转化成数组
        $response = json_decode( $response, TRUE );

        if ( is_null( $response ) ) return $this->log->write( '获取access token失败：返回数据不是json格式' );
        if ( $response[ 'msg' ] == FALSE ) return $this->log->write( '获取access token失败：' . $response[ 'info' ] );

        $this->log->write( '获取access token成功：' . print_r( $response[ 'info' ], TRUE ) );

        return $response[ 'info' ];
    }

    /**
     * 刷新access token
     *
     * @param string $refreshToken 获取用户授权时得到的refresh token
     *
     * @return mixed
     */
    public function refreshToken ($refreshToken) {

    }

    /**
     * 获取用户的openID
     *
     * @param string $accessToken 获取用户授权时得到的access token
     *
     * @return mixed    失败：FALSE，成功：长度为32位的字符串
     */
    public function getOpenID ($accessToken) {
        $url = $this->config[ $this->config[ 'base' ][ 'env' ] ][ 'oauth' ][ 'get_openid_url' ] . '?access_token=' . $accessToken;

        $this->log->write( '获取openID：' . $url );

        $response = Open::curlExec( $url );
        //转化成数组
        $response = json_decode( $response, TRUE );

        if ( is_null( $response ) ) return $this->log->write( '获取openID失败：返回数据不是json格式' );
        if ( $response[ 'msg' ] == FALSE ) return $this->log->write( '获取openID失败：' . $response[ 'info' ] );

        $this->log->write( '获取openID成功：' . $response[ 'info' ] );

        return $response[ 'info' ];
    }
}

//end of file auth.class.php