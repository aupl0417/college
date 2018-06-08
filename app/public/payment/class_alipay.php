<?php
/**
 * @author Dolen
 * @version 1.0.0
 *
 * Alipay Class
 */

//load system class
include_once(FRAMEROOT."/lib/Alipay/lib/alipay_core.php");
include_once(FRAMEROOT."/lib/Alipay/lib/alipay_md5.php");
include_once(FRAMEROOT."/lib/Alipay/lib/alipay_notify.php");
include_once(FRAMEROOT."/lib/Alipay/lib/alipay_submit.php");

class Alipay {

    private $partner         = '2088221616081063';
    private $key             = 't6f0y5ucj4iilsgg0rbnzfu0l7moa85u';
    private $transport      =  'http';

    private $seller_email   =  '2582151933@qq.com';
    private $notify_url     =  'https://pay.dttx.com/assetCenter/alipayNotifyUrl';
    private $return_url     =  'https://pay.dttx.com/assetCenter/alipayRetrunUrl';

    private $alipay_config  = '';
    private $u_nick         = '';

    public function __construct() {
        $this->alipay_config =  array(
            'partner'           => $this->partner,
            'key'               => $this->key,
            'sign_type'         => strtoupper('MD5'),
            'input_charset'     => strtolower('utf-8'),
            'cacert'            => getcwd().'\\cacert.pem',
            'transport'         => $this->transport,
        );
    }


    //request Alipay
    public function pay($ca_id,$money,$userNick){
            //支付类型
            $payment_type = "1";
            //必填，不能修改
            //服务器异步通知页面路径
            $notify_url = $this->notify_url;
            //需http://格式的完整路径，不能加?id=123这类自定义参数

            //页面跳转同步通知页面路径
            $return_url = $this->return_url;
            //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

            //商户订单号
            $out_trade_no =  $ca_id;
            //商户网站订单系统中唯一订单号，必填

            //订单名称
            $subject = '〖'.$userNick.'〗交易金额￥'.$money.'元';
            //必填

            //付款金额
            $total_fee = trim($money);
            //必填

            //订单描述
            $body = $out_trade_no;

            //商品展示地址
            $show_url = 'http://'.$_SERVER['HTTP_HOST'].'/recharge';
            //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

            //防钓鱼时间戳
            $anti_phishing_key = "";
            //若要使用请调用类文件submit中的query_timestamp函数

            //客户端的IP地址
            $exter_invoke_ip = "";
            //非局域网的外网IP地址，如：221.0.0.1

            /************************************************************/

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service"           => "create_direct_pay_by_user",
                "partner"           => trim($this->partner),
                "seller_email"      => trim($this->seller_email),
                "payment_type"      => $payment_type,
                "notify_url"        => $notify_url,
                "return_url"        => $return_url,
                "out_trade_no"      => $out_trade_no,
                "subject"           => $subject,
                "total_fee"         => $total_fee,
                "body"              => $body,
                "show_url"          => $show_url,
                "anti_phishing_key" => $anti_phishing_key,
                "exter_invoke_ip"   => $exter_invoke_ip,
                "_input_charset"    => trim(strtolower('utf-8'))
            );


            //建立请求
            $alipaySubmit = new AlipaySubmit($this->alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "正在努力跳转至支付页面……");
            header('Content-Type:text/html;charset=utf-8');
            echo $html_text;
    }



    //同步通知
    public function return_url(){

        $db = new MySql();

        if(isset($_GET['Files'])){
            unset($_GET['Files']);
        }
        if(isset($_GET['PHPSESSID'])){
            unset($_GET['PHPSESSID']);
        }

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config);

        $verify_result = $alipayNotify->verifyReturn();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号

            $out_trade_no = $_GET['out_trade_no'];
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];

            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                try{
                    $db->beginTRAN();
                    $account = new account($db);

                    $sql = "select * from pay_account_cash_in WHERE ci_caid = '".$out_trade_no."' and ci_state = '0' limit 1";
                    $orderInfo = $db->getRow($sql);

                    if(empty($orderInfo)){
                        throw new Exception('-3');
                    }

                    if( !$account->transferCash('1010102', $orderInfo['ci_uid'], $orderInfo['ci_userNick'], $aid = '', 1, $_GET['total_fee'], '', 0, '', '支付宝订单号'.$trade_no , '', '')){
                        throw new Exception($account->getError());
                    }

                    //更新充值详细表状态
                    $p = array(
                        'ci_successTime' => $_GET['notify_time'],
                        'ci_thirdOrderId' => $trade_no,
                        'ci_thirdAccount' => $_GET['buyer_email'],
                        'ci_state'          => 1,
                        'ci_transId' => $account->getLastId(),
                    );
                    if( $db->update('pay_account_cash_in',$p,"ci_caid='".$out_trade_no."'") != 1){
                        throw new Exception('-1');
                    }

                    $db->commitTRAN();
                    $text ='充值成功';
                }
                catch(Exception $e){

                    $db->rollBackTRAN();
                    $text ='您已支付成功，系统繁忙，可能到账稍有延时，请稍侯查看。';
                }
                return $text;
            }
            //echo "验证成功<br />";

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //log::writeLogMongo( 5050, 't_account_cash_tran', '', '返回交易验证失败,支付宝充值成功，系统没有处理现金账户');
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            //echo "验证失败";

            return ('验证失败');
        }

    }




    //异步通知
    public function notify_url(){

        $db = new MySql();

        if(isset($_POST['Files'])){
            unset($_POST['Files']);
        }
        if(isset($_POST['PHPSESSID'])){
            unset($_POST['PHPSESSID']);
        }

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        //log::writeLogMongo( 123456333, 't_account_cash_tran', '1234444', $_POST);

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            $account = new account($db);
            $db->beginTRAN();

            //log::writeLogMongo( 12345655555, 't_account_cash_tran', '12345555', $_POST);

            if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                try{

                    $sql = "select * from pay_account_cash_in WHERE ci_caid = '".$out_trade_no."' and ci_state = '0' limit 1";
                    $orderInfo = $db->getRow($sql);

                    log::writeLogMongo( 552321, 't_account_cash_tran', '559994433', $orderInfo);
                    log::writeLogMongo( 55232001, 't_account_cash_tran', '559994433', $sql);

                    if(empty($orderInfo)){
                        log::writeLogMongo( 5521, 't_account_cash_tran', '559994433', '');
                        throw new Exception('-2');
                    }

                    if( !$account->transferCash('1010102', $orderInfo['ci_uid'],  $orderInfo['ci_userNick'], '', 1, $_POST['total_fee'], '', 0, '', '支付宝订单号'.$trade_no , '', '')){
                        log::writeLogMongo( 554431312121, 't_account_cash_tran', '554433', $_POST.$account->getError());
                        throw new Exception($account->getError());
                    }

                    //更新充值详细表状态
                    $p = array(
                        'ci_successTime' => $_POST['notify_time'],
                        'ci_thirdOrderId' => $trade_no,
                        'ci_thirdAccount' => $_POST['buyer_email'],
                        'ci_state'          => 1,
                        'ci_transId' => $account->getLastId(),
                    );
                    if( $db->update('pay_account_cash_in',$p,"ci_caid='".$out_trade_no."'") != 1){
                        log::writeLogMongo( 55443322112, 't_account_cash_tran', '55443322', $_POST);
                        throw new Exception('-1');
                    }

                    $db->commitTRAN();
                }
                catch(Exception $e){
                    $db->rollBackTRAN();
                    echo "fail";
                    exit;
                }
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "success";     //请不要修改或删除

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

    public function setReturnUrl($url)
    {
        $this->return_url = $url;
    }

    public function setNotifyUrl($url)
    {
        $this->notify_url = $url;
    }


}