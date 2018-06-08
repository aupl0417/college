<?php

/**
 *
 * 功能：手机端消息推送
 * 使用：可以分别调用后面四个方法，也可以统一调用第一个方法。事实上，当调用第一个方法的时候，也是根据传的参数，选择调用后面四个方法中的某一个
 *
 * @author ranqin
 * @time   2016-07-27
 *
 */

require_once( FRAMEROOT . '/lib/xinge/XingeApp.php' );

class Xinge {

    //信鸽商户账号配置
    public $accessId = '';
    public $secretKey = '';

    const ANDROID_ACCESS_ID = '2100212575';
    const ANDROID_SECRET_KEY = '16c538eddf21c4e701f94702adc3966c';

    const IOS_ACCESS_ID = '2200212576';
    const IOS_SECRET_KEY = '623db6be08712dcfdd73d722b68c6209';

    /*调用SDK XingeApp 对应静态的方法*/
    public static function __callStatic ( $name, $args ) {
        $system = self::getSystem( $name );
        if ( !$system ) return FALSE;

        if ( $system == 'Android' ) {
            array_unshift( $args, self::ANDROID_ACCESS_ID, self::ANDROID_SECRET_KEY );
            return call_user_func_array( array('XingeApp', $name), $args );
        }

        if ( $system == 'Ios' ) {
            array_unshift( $args, self::IOS_ACCESS_ID, self::IOS_SECRET_KEY );
            return call_user_func_array( array('XingeApp', $name), $args );
        }
    }

    /*调用SDK XingeApp 对应普通的方法*/
    public function __call ( $name, $args ) {
        $xinge = new XingeApp( $this->accessId, $this->secretKey );
        return call_user_func_array( array($xinge, $name), $args );
    }

    /* 推送消息给所有设备的所有账号 */
    public static function PushMessageToAllDevice ( $title, $content, $iosenv ) {
        return self::PushAllAndroid( $title, $content ) && self::PushAllIos( $content, $iosenv );
    }

    /*
     * 获取操作系统类型
     */
    public static function getSystem ( $funcName ) {
        $upper = strrpos( $funcName, 'A' ) OR $upper = strrpos( $funcName, 'I' );
        return $upper ? substr( $funcName, $upper ) : FALSE;
    }

    /* 推送单个设备的方法
     * $userID     string   会员id
     * $title      string   消息标题   （ios可以不需要）
     * $content    string   消息内容
     * environment int     1-生产环境，2-测试环境  （ios必需）
    */
    public function pushSingle ( $userID, $title = '', $content = '', $environment = 1 ) {
        if ( $userID && strlen( $userID ) == 32 ) {
            $token = ( new MySql() )->getField( "SELECT u_token FROM t_app_xinge WHERE u_id = '{$userID}' LIMIT 1" );
            if($token){
                if ( strlen( $token ) == 40 ) {
                    $this->accessId = $accessId = xinge::ANDROID_ACCESS_ID;
                    $this->secretKey = $secretKey = xinge::ANDROID_SECRET_KEY;
                    $res = xinge::PushTokenAndroid( $accessId, $secretKey, $title, $content, $token );
                } else {
                    $this->accessId = $accessId = xinge::IOS_ACCESS_ID;
                    $this->secretKey = $secretKey = xinge::IOS_SECRET_KEY;
                    $res = xinge::PushTokenIos( $accessId, $secretKey, $content, $token, $environment );
                }
                return (isset($res[ 'ret_code' ]) && $res[ 'ret_code' ] == 0 ) ? '1001' : '1002'; //ret_code=0代表推送成功
            }else{
                return '1002';
            }
        }else{
            return '1002';
        }
    }
}
