<?php
/**
 * @author Dolen
 * @version 1.0.0
 *
 * weixinPay Class
 */

include_once(FRAMEROOT."/lib/weixinPay/WxPayPubHelper/WxPayPubHelper.php");

class weixinPay
{

    function micropay ($money,$userNick,$orderId)
    {
        //使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub();

        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("body", $userNick."微信消费充值");//商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
        //$out_trade_no = WxPayConf_pub::$orderId;
        $unifiedOrder->setParameter("out_trade_no", "$orderId");//商户订单号
        $unifiedOrder->setParameter("total_fee", $money * 100);//总金额
        //$unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
        $unifiedOrder->setParameter("notify_url", "https://pay.dttx.com/assetCenter/weixinNotifyUrl");//通知地址
        $unifiedOrder->setParameter("trade_type", "NATIVE");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();

        //商户根据实际情况设置相应的处理流程,此处仅作举例
        if ($unifiedOrderResult["return_code"] == "FAIL") {
            echo "通信出错：" . $unifiedOrderResult['return_msg'] . "<br>";
        } elseif ($unifiedOrderResult["result_code"] == "FAIL") {
            echo "出错" . "<br>";
            echo "错误代码：" . $unifiedOrderResult['err_code'] . "<br>";
            echo "错误代码描述：" . $unifiedOrderResult['err_code_des'] . "<br>";
        } else {
            $code_url = $unifiedOrderResult["code_url"];
            return $code_url;
        }

    }


    function notify() {
        //使用通用通知接口
        $notify = new Notify_pub();

        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $db = new MySql();
            $db->beginTRAN();
            $ac = new account($db);
            try{


                log::writeLogMongo( 553332321, 't_account_cash_tran', 'weixin', $notify->data);

                $sql = "select * from pay_account_cash_in WHERE ci_caid = '".$notify->data["out_trade_no"]."' and ci_state = '0' limit 1";
                $orderInfo = $db->getRow($sql);

                if(empty($orderInfo)){
                    throw new Exception('-2');
                }

                if( !$ac->transferCash('1010101', $orderInfo['ci_uid'], $orderInfo['ci_userNick'], '', 1, $notify->data['total_fee']*0.01, '', 0, '', '微信订单号'.$notify->data["out_trade_no"] , '', '')){
                    throw new Exception($ac->getError());
                }

                //更新充值详细表状态
                $p = array(
                    'ci_successTime' => F::mytime(),
                    'ci_thirdOrderId' => $notify->data['transaction_id'],
                    'ci_state'          => 1,
                    'ci_transId' => $ac->getLastId(),
                );
                if( $db->update('pay_account_cash_in',$p,"ci_caid='".$notify->data["out_trade_no"]."'") != 1){
                    throw new Exception('-1');
                }


                $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
                log::writeLogMongo( 98765, 't_account_cash_tran', 'weixin', '充值成功');
                $db->commitTRAN();
            }
            catch(Exception $e){
                //更新充值详细表状态
                //$p = array( 'ci_reason' => '系统充值更新失败，微信支付成功');
                //$db->update('t_account_cash_in',$p,"ci_caid='".$notify->data["out_trade_no"]."'");

                $notify->setReturnParameter("return_code","FAIL");//返回状态码
                $db->rollBackTRAN();
            }
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
    }


}