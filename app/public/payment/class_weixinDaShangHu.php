<?php
/**
 * @author Dolen
 * @version 1.0.0
 *
 * 本接口类文档仅针对商户接入商赢微信API功能接口规范
 */

class weixinDaShangHu
{
    //商户号
    private $merchantNo = '990551077770003'; //测试账户
    //private $merchantNo = '990581077770001';
    //终端号
    private $terminalNo =  '77700086'; //测试
    //private $terminalNo =  '77700044';
    //支付类型（固定）微信
    private $payType =  '19';
    //支付方式（固定）二维码
    private $weChatPayType =  '603';
    //测试加密key
    private $key =  'vIWkd8sYeI';
    //private $key =  'mav2wTcjKY';

    /**
     * 微信统一下单(二维码)
     * @param
     */
    function weChatQR ( $options ){

        //请求参数组装
        $options['merchantNo'] = $this->merchantNo;
        $options['terminalNo'] = $this->terminalNo;
        $options['payType'] = $this->payType;
        $options['weChatPayType'] = $this->weChatPayType;
        $options['merchantURL'] = 'https://pay.dttx.com/assetCenter/weiYing.json';

        //签名
        $options['signMsg'] = strtoupper(MD5($this->merchantNo.$this->terminalNo.$options['payMoney'].$options['productName'].$options['inTradeOrderNo'].$this->payType.$this->weChatPayType.$this->key));

        //测试地址
        $ceshiurl = 'http://paygw.guangyinwangluo.com/swPayInterface/weChatQR';
        //正式地址
        $zhengshiurl = 'http://paygw.sanwing.com/swPayInterface/weChatQR';

        return json_decode($this->curlPost($zhengshiurl,json_encode($options)));

    }


    //微信支付异步通知
    function notify (){
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];

        $data2 = json_decode($data);
        //log::writeLogMongo( 225921314145572, 'chongzhi', 2,  $data2->inTradeOrderNo);


        $signValue = strtoupper(MD5( $data2->webOrderId.$data2->inTradeOrderNo.$data2->tradeStatus.$this->key));

        //log::writeLogMongo( 98765999944499995966699, 't_account_cash_tran', 'weixin', $signValue);
        if($signValue == $data2->signMsg && $data2->tradeStatus == 'SUCCESS'){

            //log::writeLogMongo( 98765999944499995999, 't_account_cash_tran', 'weixin', '$signValue');
            $db = new MySql();
            $db->beginTRAN();
            $ac = new account($db);
            try{
                //取出充值异动数据
                $sql = "SELECT * FROM pay_account_cash_in WHERE ci_money = $data2->totalFee*0.01 and ci_state = 0 and ci_caid like '".$data2->inTradeOrderNo."%' limit 1";
                $orderInfo = $db->getRow($sql);

                //log::writeLogMongo( 9876599994449999999, 't_account_cash_tran', 'weixin', $orderInfo);

                if(empty($orderInfo)){
                    throw new Exception('-2');
                }

                if( !$ac->transferCash('1010105', $orderInfo['ci_uid'], $orderInfo['ci_userNick'], '', 1, $data2->totalFee*0.01, '', 0, '', '(中付)微信订单号'.$data2->inTradeOrderNo , '', '')){
                    //log::writeLogMongo( 852369, 't_account_cash_tran', 'weixin', 'ddddddddddddddddddd');
                    throw new Exception($ac->getError());
                }

                //更新充值详细表状态
                $p = array(
                    'ci_successTime' => F::mytime(),
                    'ci_thirdOrderId' => $data2->webOrderId,
                    'ci_state'          => 1,
                    'ci_transId' => $ac->getLastId(),
                );
                if( $db->update('pay_account_cash_in',$p,"ci_caid='".$orderInfo['ci_caid']."'") != 1){

                    //log::writeLogMongo( 85236945, 't_account_cash_tran', 'weixin', 'ffff'.$db->lastSql());

                    //"UPDATE pay_account_cash_in SET ci_successTime='2016-09-23 17:23:08',ci_thirdOrderId='1280096',ci_state='1',ci_transId='2016092317230805796340796' WHERE ci_caid='2016092317225103829'";
                    throw new Exception('-1');
                }

                log::writeLogMongo( 9876599999999999, 't_account_cash_tran', 'weixin', '充值成功'.$data2->inTradeOrderNo);
                $db->commitTRAN();
            }
            catch(Exception $e){
                //更新充值详细表状态
                log::writeLogMongo( 98765999999999989, 't_account_cash_tran', 'weixin', '充值失败'.$data2->inTradeOrderNo);
                $db->rollBackTRAN();
            }
        }

    }







    /**
     * 支付统一下单(二维码)
     * @param
     */
    function zfpayQR ( $options ) {

        //请求参数组装
        $options['merchantNo'] = $this->merchantNo;
        $options['terminalNo'] = $this->terminalNo;
        $options['payType'] = 18;//固定
        $options['alipayPayType'] = 603;//固定
        $options['validDate'] = '1c';
        $options['merchantURL'] = 'https://pay.dttx.com/assetCenter/weiYingAlipay.json';

        //签名
        $options['signMsg'] = strtoupper(MD5($this->merchantNo.$this->terminalNo.$options['payMoney'].$options['inTradeOrderNo'].$options['payType'].$options['alipayPayType'].$options['validDate'].$this->key));

        //正式地址
        $zhengshiurl = 'http://paygw.sanwing.com/swPayInterface/zfpayQR';

        return json_decode($this->curlPost($zhengshiurl,json_encode($options)));
    }


    private function curlPost($url,$data,$param=null){
        $curl = curl_init($url);// 要访问的地址
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }


}