<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.4
 * 修改日期：2016-03*08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*****************

 *如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 *1、开发文档中心（https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.KvddfJ&treeId=62&articleId=103740&docType=1）
 *2、商户帮助中心（https://cshall.alipay.com/enterprise/help_detail.htm?help_id=473888）
 *3、支持中心（https://support.open.alipay.com/alipay/support/index.htm）

 *如果想使用扩展功能,请按文档要求,自行添加到parameter数组即可。
 **********************************************
 */

define( 'SDK_ROOT', FRAMEROOT . '/lib/appalipay' );

require( SDK_ROOT . '/lib/alipay_submit.class.php' );

class App_Alipay {

    private $alipay_config = NULl;

    public function __construct () {

        require( SDK_ROOT . '/alipay.config.php' );

        //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
        $alipay_config[ 'partner' ] = '2088221616081063';

        //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
        $alipay_config[ 'seller_id' ] = '2582151933@qq.com';

        //商户的私钥,此处填写原始私钥，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
        $alipay_config[ 'private_key_path' ] = SDK_ROOT . '/key/rsa_private_key_pkcs8.pem';

        //支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
        $alipay_config[ 'ali_public_key_path' ] = SDK_ROOT . '/key/alipay_public_key.pem';

        // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $alipay_config[ 'notify_url' ] = "https://mob.dttx.com/users/alipayNotify.json";

        // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        //$alipay_config[ 'return_url' ] = "http://pay.af888.com/assetCenter/alipayNotifyUrl";

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config[ 'cacert' ] = SDK_ROOT . '/cacert.pem';

        // 产品类型
        $alipay_config[ 'service' ] = "mobile.securitypay.pay";

        $this->alipay_config = $alipay_config;
    }

    //构造支付请求参数
    public function pay ( $ca_id, $money, $userNick = '' ) {
        /**************************请求参数**************************/
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $ca_id;

        //订单名称，必填
        $subject = "【{$userNick}】交易金额￥{$money}元";

        //付款金额，必填
        $total_fee = $money;

        //商品描述，可空
        $body = '交易';


        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => $this->alipay_config[ 'service' ],
            "partner" => $this->alipay_config[ 'partner' ],
            "seller_id" => $this->alipay_config[ 'seller_id' ],
            "payment_type" => $this->alipay_config[ 'payment_type' ],
            "notify_url" => $this->alipay_config[ 'notify_url' ],

            'show_url' => 'm.alipay.com',
            'it_b_pay' => '30m',

            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "_input_charset" => trim( strtolower( $this->alipay_config[ 'input_charset' ] ) )
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"

        );

        //建立请求
        $alipaySubmit = new AlipaySubmit( $this->alipay_config );
        return $params = $alipaySubmit->buildRequestPara( $parameter );
    }

    //获取配置
    public function getAlipayConfig () {
        return $this->alipay_config;
    }
}