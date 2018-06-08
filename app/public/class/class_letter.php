<?php

/**
 *
 * 邮件
 * @author flybug
 * @version 1.0.0
 *
 * @rewrite vc
 */
class letter {

    private $l_errNum = NULL; //错误代码 -1:非邮件格式、-2：频繁发送、-3：发送失败、-4：参数不正确、-5：选择不存在模板、-6：验证码已失效、-60：验证码验证失败、-61：验证错误次数超过5次，验证码已被删除
    private $subject = '大唐天下';      //默认标题

    public function __construct() {

    }

    /**
     * 发送邮件内容
     * $email   email地址     必填
     * $nick    用户名称      必填
     * $tempId  模板号     【1、发送验证码；2、注册账号邮件;3、邮箱认证；4、密码重置邮件；5、意见反馈】
     * $data    附加内容【数组形式，比如array('url'=>www.dttx.com)】
    */
//    function send($email,$nick, $tempId = 1,$data = array()){
//        if (!F::isEmail($email)) {
//            $this->l_errNum = -1;
//            return false;
//        }
//        if(!F::isNotNull($nick)){
//            $this->l_errNum = -4;
//            return false;
//        }
//
//        //防止频繁发送，间隔需要120秒(放在cache里)
//        $cache = new cache();
//        $cacheCode = $cache->get('mailCode_'.$email);
//
//        if (!$cacheCode) {   //如果此号码没有发送码记录，则set
//            $code = rand(100000, 999999);
//            $info = array('code'=>$code,'ctime'=>time());
//            $cache->set('mailCode_'.$email, $info, 60*60*24);
//        }else {     //有缓存信息表示SMS_SENDINTERVAL时间内多次操作
//            if(($cacheCode['ctime'] + SMS_SENDINTERVAL) > time()){
//                $this->l_errNum = -2;//发送过于频繁
//                return false;
//            }else{
//                //重新发送，就重新生成验证码
//                $code = rand(100000, 999999);
//                $info = array('code'=>$code,'ctime'=>time());
//                $cache->set('mailCode_'.$email, $info, 60*60*24);    //验证码保存24小时
//            }
//        }
//        //注册邮件，多传递一个email参数
//        if($tempId == 2){
//            $data['email'] = $email;
//        }
//
//        $body = $this->mailBody($nick,$code,$tempId,$data);
//        if(!$body){
//            return false;
//        }
//
//        if ($this->sendmail(array('to'=>$email,'body'=>$body))) {
//            return true;
//        } else {
//            $this->l_errNum = -3;
//            return false;
//        }
//    }

    /**
     * 队列调用的发送方法
    */
    public function send($email,$nick,$code, $tempId,$data){
        //注册邮件，多传递一个email参数
        if($tempId == 2){
            $data['email'] = $email;
        }

        $body = $this->mailBody($nick,$code,$tempId,$data);
        if(!$body){
            return false;
        }

        if ($this->sendmail($email,$body)) {
            return true;
        } else {
            $this->l_errNum = -3;
            return false;
        }
    }

    /**
     * 邮箱发送内容
     * $tempId  1、发送验证码；2、注册账号邮件;3、邮箱认证；4、密码重置邮件；5、意见反馈
    */
    private function mailBody($nick,$code,$tempId,$data){
        $time = date('Y-m-d');
        $body = '<div style="border:1px #ccc solid;width:600px;">
                    <div style="width:100%;height:70px;background:#1766c8;">
                        <img style="margin-left: 20px;" width="202" height="69" title="" alt="大唐天下" src="https://www.dttx.com/app/www/template/cn/share/themes/default/images/erp_logo.png" />
                    </div>
                    <div style="margin:14px;color:black;font-size:14px;">
                        <p>
                            <span>亲爱的大唐天下用户</span>
                            <a href="https://u.dttx.com/" target="_blank" style="color:#3598DC">'.$nick.'</a>
                        </p>';
        switch ($tempId){

            case -4:    //测试模板
                $this->subject = '大唐天下验证码';
                $body .= '
                            <p>
                                <h4>hello 您的验证码是：<span style="color:#3598DC;font-size:20px;">'.$code.'</span></h4>
                            </p>
                            <p style="color:#666;font-size:12px;">
                                此验证码24小时有效，请尽快完成操作，如果您忽略这条信息，请重新发送邮箱验证码。
                            </p>';
                break;
            case 1:
                $this->subject = '大唐天下验证码';
                $body .= '
                            <p>
                                <h4>您的验证码是：<span style="color:#3598DC;font-size:20px;">'.$code.'</span></h4>
                            </p>
                            <p style="color:#666;font-size:12px;">
                                此验证码24小时有效，请尽快完成操作，如果您忽略这条信息，请重新发送邮箱验证码。
                            </p>';
                break;
            case 2:
                $this->subject = '大唐天下注册验证码';
                $body .= '<p>
                            <div>
                                感谢你注册大唐天下商业大系统！
                            </div>
                            <div style="margin-top:10px;">	
                                您的用户名：<span style="color:#3598DC;margin-right:20px;">'.$nick.'</span>
                                <span>您的注册邮箱：<span style="color:#3598DC;">'.$data['email'].'</span></span>
                            </div>
                        </p>
                        <p>
                            <h4>您的注册验证码为：<b style="color:#3598DC;font-size:20px;">'.$code.'</b>， 请在注册的验证码输入框中输入以完成注册。</h4>
                        </p>
                        <p style="color:#666;font-size:12px;">
                            此验证码24小时有效，请尽快完成操作，如果您忽略这条信息，请重新发送邮箱验证码。
                        </p>';
                break;
            case 3:
                $url = isset($data['url'])?$data['url']:'';
                if($url == ''){
                    $this->l_errNum = -4;
                    return false;
                }
                $this->subject = '大唐天下邮箱认证';
                $body .= '<p style="color:#000;font-size:12px;">
                            我们收到您的邮箱认证请求，点击下面的链接即可认证邮箱
                        </p>
                        <a style="color:#3598DC;font-size:12px;word-wrap:break-word;" href="'.$url.'">'.$url.'</a>
                        <p style="color:#666;font-size:12px;">
                            此链接24小时有效，请尽快完成操作，认证完成后邮件将失效。
                        </p>';
                break;
            case 4:
                $url = isset($data['url'])?$data['url']:'';
                if($url == ''){
                    $this->l_errNum = -4;
                    return false;
                }
                $this->subject = '大唐天下密码重置邮件';
                $body .= '<p style="color:#000;font-size:12px;">
                            我们收到您的密码重置请求，点击下面的链接即可重置您的密码
                        </p>
                        <a style="color:#3598DC;font-size:12px;word-wrap:break-word;" href="'.$url.'">'.$url.'</a>
                        <p style="color:#666;font-size:12px;">
                            此链接24小时有效，请尽快完成操作，重置完成后邮件将失效。
                        </p>';
                break;
            case 5:
                $content = isset($data['content'])?$data['content']:'';
                if($content == ''){
                    $this->l_errNum = -4;
                    return false;
                }
                $this->subject = '大唐天下意见反馈';
                $body .= '<p style="color:#333;font-size:14px;">
                            我们已经收到您反馈的意见，感谢您提出宝贵意见，我们的处理方式如下：
                            <div style="color:#222;font-size:14px;font-weight:bold;">'.$content.'</div>
                        </p>
                        <p style="color:#333;font-size:12px;margin-top:30px;">
                            如有疑问，请联系我们的客服，客服电话：95083
                        </p>';
                break;
            default:
                $this->l_errNum = -5;
                return false;
        }
        $body .= '      <div style="margin-top:90px;position:relative;height:100px;">
                            <div style="position:relative;top:0;right:10px;float:right;">
                                <h2>大唐天下</h2>
                                <div style=" position:absolute;right:3px;">'.$time.'</div>
                            </div>
                        </div>
                    </div>
                </div>';
        return $body;
    }
    
    //发送动作
    private function sendmail($to,$body){

        $cfg['smtp']     = 'smtp.dttx.com';
        $cfg['port']     = 25;
        $cfg['username'] = 'dttx@dttx.com';
        $cfg['password'] = 'dttx!@#$%0001';
        $cfg['email']    = 'dttx@dttx.com';

//        $cfg['smtp']     = 'smtp.exmail.qq.com';
//        $cfg['port']     = 25;
//        $cfg['username'] = 'qjw@af888.com';
//        $cfg['password'] = 'Dttx123';
//        $cfg['email']    = 'qjw@af888.com';

        //使用该函数前请先加载PHPMAILER类
        $host   = $cfg['smtp'];
        $port   = $cfg['port'];
        $user   = $cfg['username'];
        $readto = '';
        $from   = $cfg['email'];
        $att    = '';
        $reto   = '';
        $cc     = '';
        $bcc    = '';
        $from_name  = '大唐天下';
        $reto_name  = '';
        $password   = $cfg['password'];
        $to_name    = 'aas';
        $subject    = $this->subject;
        $charset    = 'utf-8';

        include_once(FRAMEROOT."/lib/phpmailer/class.phpmailer.php");
        $mail = new \PHPMailer();

        $mail->CharSet	  =$charset;
        $mail->IsSMTP();							// tell the class to use SMTP
        $mail->SMTPAuth   = true;					// enable SMTP authentication
        //$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
        $mail->Port       = $port;                  // set the SMTP server port
        $mail->Host       = $host;					// SMTP server
        $mail->Username   = $user;					// SMTP server username
        $mail->Password   = $password;				// SMTP server password
        if($readto) $mail->ConfirmReadingTo=$readto;				//读后通知邮箱
        if($reto) $mail->AddReplyTo($reto,$reto_name);  //回复
        if($cc) $mail->AddCC($cc);  //密送
        if($bcc) $mail->AddBCC($bcc); //抄送
        $mail->From=$from;
        $mail->FromName=$from_name;
        $mail->AddAddress($to,$to_name);
        $mail->Subject  = $subject;
        $mail->AltBody    = "这是一封HTML邮件，请用HTML方式浏览!";
        $mail->WordWrap   = 80;
        $mail->MsgHTML($body);
        $mail->IsHTML(true);
        if($att) {
            if(!is_array($att)) $att[0]=$att;
            foreach($att as $val){
                $mail->AddAttachment($val); //附件
            }
        }

        if(!$mail->Send()){
            return false;
        }else{
            return true;
        }

    }

    //根据索引得到模版认证短信的验证码进行验证
    public function TestValidateByIndex($email, $code) {
        $cache = new cache();
        $cacheCode = $cache->get('mailCode_'.$email);
        if (!$cacheCode) {
            $this->l_errNum = -6;
            return false;
        }

        $errTimes = isset($cacheCode['errTimes'])?$cacheCode['errTimes']:0;
        //检查验证次数
        if($errTimes >= 5 ){
            $this->l_errNum = -61;
            $cache->del('mailCode_'.$email); //清除缓存
            return false;
        }

        //检测验证码
        if($cacheCode['code'] == $code){    //匹配成功
            $cache->del('mailCode_'.$email); //清除缓存
            return true;
        }else{
            $this->l_errNum = -60;
            //累加错误次数
            $data = array(
                'code'=>$cacheCode['code'],
                'ctime'=>time(),
                'errTimes'=>$cacheCode['errTimes'] + 1
            );
            $cache->set('mailCode_'.$email, $data, 60*60*24);
            return false;
        }
    }

    //获取错误代码
    public function getErrNum(){
        return $this->l_errNum;
    }

}
