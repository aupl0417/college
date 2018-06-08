<?php
require_once('../../../config.php');
function kq_ck_null($kq_va, $kq_na) {
    if ($kq_va == "") {
        return $kq_va = "";
    } else {
        return $kq_va = $kq_na . '=' . $kq_va . '&';
    }
}

function  kqConfigFormate($arr){
    if (is_array($arr)) {
        $string = '';
        foreach ($arr as $key => $value) {
            if (!empty($value)) {
                $string .= "{$key}={$value}&";
            }
        }
        return substr($string,0,strlen($string)-1);
    }else{
        return false;
    }
}

//人民币网关账号，该账号为11位人民币网关商户编号+01,该值与提交时相同。
$kq_check_all_para = kq_ck_null($_REQUEST['merchantAcctId'], 'merchantAcctId');
//网关版本，固定值：v2.0,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['version'], 'version');
//语言种类，1代表中文显示，2代表英文显示。默认为1,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['language'], 'language');
//签名类型,该值为4，代表PKI加密方式,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['signType'], 'signType');
//支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['payType'], 'payType');
//银行代码，如果payType为00，该值为空；如果payType为10,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['bankId'], 'bankId');
//商户订单号，,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['orderId'], 'orderId');
//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101,该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['orderTime'], 'orderTime');
//订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试,该值与支付时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['orderAmount'], 'orderAmount');
// 快钱交易号，商户每一笔交易都会在快钱生成一个交易号。
$kq_check_all_para.=kq_ck_null($_REQUEST['dealId'], 'dealId');
//银行交易号 ，快钱交易在银行支付时对应的交易号，如果不是通过银行卡支付，则为空
$kq_check_all_para.=kq_ck_null($_REQUEST['bankDealId'], 'bankDealId');
//快钱交易时间，快钱对交易进行处理的时间,格式：yyyyMMddHHmmss，如：20071117020101
$kq_check_all_para.=kq_ck_null($_REQUEST['dealTime'], 'dealTime');
//商户实际支付金额 以分为单位。比方10元，提交时金额应为1000。该金额代表商户快钱账户最终收到的金额。
$kq_check_all_para.=kq_ck_null($_REQUEST['payAmount'], 'payAmount');
//费用，快钱收取商户的手续费，单位为分。
$kq_check_all_para.=kq_ck_null($_REQUEST['fee'], 'fee');
//扩展字段1，该值与提交时相同
$kq_check_all_para.=kq_ck_null($_REQUEST['ext1'], 'ext1');
//扩展字段2，该值与提交时相同。
$kq_check_all_para.=kq_ck_null($_REQUEST['ext2'], 'ext2');
//处理结果， 10支付成功，11 支付失败，00订单申请成功，01 订单申请失败
$kq_check_all_para.=kq_ck_null($_REQUEST['payResult'], 'payResult');
//错误代码 ，请参照《人民币网关接口文档》最后部分的详细解释。
$kq_check_all_para.=kq_ck_null($_REQUEST['errCode'], 'errCode');

$cfg_99bill = array(
    //编码方式，1代表 UTF-8; 2 代表 GBK; 3代表 GB2312 默认为1,该参数必填。
    'inputCharset'=>'1',
    //接收支付结果的页面地址，该参数一般置为空即可。
    'pageUrl'=>"",
    //服务器接收支付结果的后台地址，该参数务必填写，不能为空。
    'bgUrl'=>'https://user.youpinshiyong.com/include/lib/99bill/recieve.php',
    //网关版本，固定值：v2.0,该参数必填。
    'version'=>$_REQUEST['version'],
    //语言种类，1代表中文显示，2代表英文显示。默认为1,该参数必填。
    'language'=>$_REQUEST['language'],
    //签名类型,该值为4，代表PKI加密方式,该参数必填。
    'signType'=>$_REQUEST['signType'],
    //人民币网关账号，该账号为11位人民币网关商户编号+01,该参数必填。
    'merchantAcctId'=>$_REQUEST['merchantAcctId'],
    //支付人姓名,可以为空。
    'payerName'=>"",
    //支付人联系类型，1 代表电子邮件方式；2 代表手机联系方式。可以为空。
    'payerContactType'=>"2",
    //支付人联系方式，与payerContactType设置对应，payerContactType为1，则填写邮箱地址；payerContactType为2，则填写手机号码。可以为空。
    'payerContact'=>"",

    //商户订单号，以下采用时间来定义订单号，商户可以根据自己订单号的定义规则来定义该值，不能为空。
    'orderId'=>$_REQUEST['orderId'],
//    'orderId'=>'20150323113910',
    //订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试。该参数必填。
    'orderAmount'=>$_REQUEST['orderAmount'],
    //订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101，不能为空。
    'orderTime'=>$_REQUEST['orderTime'],
//    'orderTime'=>'20150323113910',

    //商品名称，可以为空。
    'productName'=>"",
    //商品数量，可以为空。
    'productNum'=>'',
    //商品代码，可以为空。
    'productId'=>'',
    //商品描述，可以为空。
    'productDesc'=>'',
    //扩展字段1，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
    'ext1'=>'',
    //扩展自段2，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
    'ext2'=>$_REQUEST['ext2'],
    //支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10，必填。
    'payType'=>'00',
    //银行代码，如果payType为00，该值可以为空；如果payType为10，该值必须填写，具体请参考银行列表。
    'bankId'=>'',
    //同一订单禁止重复提交标志，实物购物车填1，虚拟产品用0。1代表只能提交一次，0代表在支付不成功情况下可以再提交。可为空。
    'redoFlag'=>'',
    //快钱合作伙伴的帐户号，即商户编号，可为空。
    'pid'=>''
);

$trans_body = substr($kq_check_all_para, 0, strlen($kq_check_all_para) - 1);
//取消单笔交易日志记录 By Kwan 2015年3月25日19:51:25
//file_put_contents('log_kq_'.date('YmdHis').'.txt',$_REQUEST['ext1'].'||'.(kqConfigFormate($cfg_99bill)));

    if (md5(kqConfigFormate($cfg_99bill)) == $_REQUEST['ext1']) {


        $MAC = base64_decode($_REQUEST['signMsg']);

        $fp = fopen("./99bill.cert.rsa.20340630.cer", "r");
        $cert = fread($fp, 8192);
        fclose($fp);
        $pubkeyid = openssl_get_publickey($cert);
        $ok = openssl_verify($trans_body, $MAC, $pubkeyid);


        if ($ok == 1) {
            switch ($_REQUEST['payResult']) {
                case '10':
                    //此处做商户逻辑处理
                    $rtnOK = 1;
                    //以下是我们快钱设置的show页面，商户需要自己定义该页面。

                    $acc = new account();
                    $acc->updateAccountChangeState($_REQUEST['orderId'],$_REQUEST['dealId'],0);



                    $rtnUrl = "https://user.youpinshiyong.com/include/lib/99bill/show.php?msg=success";
                    break;
                default:
                    $rtnOK = 0;
                    //以下是我们快钱设置的show页面，商户需要自己定义该页面。
                    $rtnUrl = "https://user.youpinshiyong.com/include/lib/99bill/show.php?msg=false";
                    break;
            }
        } else {
            $rtnOK = 0;
            //以下是我们快钱设置的show页面，商户需要自己定义该页面。
            $rtnUrl = "https://user.youpinshiyong.com/include/lib/99bill/show.php?msg=error";
        }
    }else{
        $rtnOK = 0;
        //以下是我们快钱设置的show页面，商户需要自己定义该页面。
        $rtnUrl = "http://user.youpinshiyong.com/include/lib/99bill/show.php?msg=error";
    }





?>

<result><?PHP echo $rtnOK; ?></result> <redirecturl><?PHP echo $rtnUrl; ?></redirecturl>