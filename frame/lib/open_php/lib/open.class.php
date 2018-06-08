<?php
/**
 * Class Open
 *
 * @package com\dttx\www
 */

namespace com\dttx\www;

class Open {

    //配置
    protected $config = NULL;

    public function __construct () {
        //获取配置
        $this->config = require( LIB_PATH . '/config.php' );
    }

    /**
     * @param string      $api
     * @param array|null  $params
     * @param string|null $accessToken
     *
     * @return array
     * @throws \Exception
     */
    public function request ($api, $params = NULL, $accessToken = NULL) {
        if ( empty( $api ) ) throw new \Exception( '参数$api为空' );
        if ( !is_string( $api ) ) throw new \Exception( '参数$api不是字符串' );

        if ( $params ) if ( !is_array( $params ) ) throw new \Exception( '参数$params必须是数组' );

        $params[ 'partner_id' ] = $this->config[ 'base' ][ 'partner_id' ];
        $params[ 'app_key' ] = $this->config[ 'base' ][ 'app_id' ];
        $params[ 'timestamp' ] = time();
        $accessToken AND $params[ 'access_token' ] = $accessToken;
        $params[ 'sign' ] = self::getSignValue( $params, $this->config[ 'base' ][ 'secret_key' ] );

        return self::curlExec( $this->config[ $this->config[ 'base' ][ 'env' ] ][ 'open' ][ 'api_url' ] . trim( $api, '/' ), $params );
    }

    /**
     * @param array  $params
     * @param string $secretKey
     *
     * @return string
     */
    protected static function getSignValue (&$params, $secretKey) {
        ksort( $params );

        return md5( self::http_build_string( $params ) . "&$secretKey" );
    }

    /**
     * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
     *
     * @param array $array 需要组合的数组
     *
     * @return string
     */
    protected static function http_build_string (&$array) {
        $string = '';
        foreach ($array as $key => $val) {
            $string .= "{$key}={$val}&";
        }

        //去掉最后一个连接符
        return $string ? substr( $string, 0, -1 ) : '';
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool|mixed
     */
    public static function curlExec ($url, &$params) {
        //默认配置
        $curl_conf = array (
            CURLOPT_URL => $url,     //请求url

            CURLOPT_POST => TRUE,     //使用post方式

            CURLOPT_POSTFIELDS     => $params, //post参数
            CURLOPT_HEADER         => FALSE,   //不输出头信息
            CURLOPT_RETURNTRANSFER => TRUE,    //不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 5        // 连接超时时间
        );

        $data = FALSE;
        try {
            //初始化一个curl句柄
            $curl_handle = curl_init();
            //设置curl的配置项
            curl_setopt_array( $curl_handle, $curl_conf );
            //发起请求
            $data = curl_exec( $curl_handle );
            if ( $data === FALSE ) {
                throw new \Exception( 'CURL ERROR: ' . curl_error( $curl_handle ) );
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        curl_close( $curl_handle );

        return $data;
    }
}