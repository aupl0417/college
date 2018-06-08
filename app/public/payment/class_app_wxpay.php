<?php

include_once( FRAMEROOT . '/lib/WxpayAPI_php_v3/lib/WxPay.Api.php' );

class App_Wxpay {

    /**
     * 模式一
     */
    public function payByModelOne () {
        //...
    }

    //模式二
    /**
     * 流程：
     * 1、调用统一下单，取得code_url，生成二维码
     * 2、用户扫描二维码，进行支付
     * 3、支付完成之后，微信服务器会通知支付成功
     * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
     */
    public function payByModelTwo ( $cashinID, $money, $userNick ) {
        $input = new WxPayUnifiedOrder();
        $input->SetBody( "【{$userNick}】微信支付" );
        $input->SetAttach( "微信支付付款" );
        $input->SetOut_trade_no( $cashinID );
        $input->SetTotal_fee( $money * 100 );
        $input->SetTime_start( date( "YmdHis" ) );
        $input->SetTime_expire( date( "YmdHis", time() + 600 ) );
        $input->SetTrade_type( "APP" );
        //微信统一下单
        $result = WxPayApi::unifiedOrder( $input );

        //下单失败，返回错误信息
        if ( $result[ 'return_code' ] !== 'SUCCESS' ) return $result[ 'return_msg' ];
        if ( $result[ 'result_code' ] !== 'SUCCESS' ) return $result[ 'err_code_des' ];

        //下单成功，构造app端响应数据
        unset( $input );
        $app[ 'appid' ] = WxPayConfig::APPID;
        $app[ 'partnerid' ] = WxPayConfig::MCHID;
        $app[ 'prepayid' ] = $result[ 'prepay_id' ];
        $app[ 'package' ] = 'Sign=WXPay';
        $app[ 'noncestr' ] = WxPayApi::getNonceStr();
        $app[ 'timestamp' ] = strval( time() );

        $input = new WxPayResults();
        $input->FromArray( $app );
        $app[ 'sign' ] = $input->MakeSign();

        return $app;
    }
}