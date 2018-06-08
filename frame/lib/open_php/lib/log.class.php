<?php
namespace com\dttx\www;
class Log {

    //日志文件路径
    protected $fileName = NULL;

    //是否记录日志
    protected $isLog = TRUE;

    public function __construct () {
        $config = require( LIB_PATH . '/config.php' );
        $this->isLog = $config[ 'base' ][ 'isLog' ];

        if ( $config[ 'base' ][ 'isLog' ] ) {
            $fileName = APP_PATH . '/oauth/' . date( 'Ymd' ) . '.log';
            if ( file_exists( $fileName ) ) {
                if ( is_file( $fileName ) ) {
                    if ( is_writable( $fileName ) ) {
                        $this->fileName = $fileName;
                    } else {
                        exit( '日志文件不可写，请检查其读写权限<br />' . $fileName );
                    }
                } else {
                    exit( '已存在的日志文件不是一个常规文件<br/>文件路径：' . $fileName );
                }
            } else {
                //创建文件
                if ( !fopen( $fileName, 'w' ) ) {
                    exit( '创建日志文件失败，请检查当前目录的读写权限<br/>文件路径：' . $fileName );
                } else {
                    $this->fileName = $fileName;
                }
            }
        }

        unset( $config );
    }

    public function write ($string) {
        if ( !$this->isLog ) return FALSE;
        $line = '----------' . date( 'Y-m-d H:i:s' ) . '----------' . "\r\n";
        file_put_contents( $this->fileName, $line . $string . "\r\n", FILE_APPEND );

        return FALSE;
    }
}