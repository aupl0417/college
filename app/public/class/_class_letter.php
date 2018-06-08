<?php

/**
 *
 * 站内信
 * @author flybug
 * @version 1.0.0
 */
class letter {

    private $l_body = NULL; //信件内容
    private $l_title = NULL; //信件标题
    private $l_to = NULL; //收件人：全部买家bur、全部卖家ser、全部all、群发mass
    private $l_from = NULL; //发件人
    private $l_paper = NULL; //信纸编号；根据信件的编号拼接模板名称，去调用模板；
    private $l_tos = NULL; //收件人nick拼接的‘,’分割串
    private $l_errNum = NULL; //错误代码 -1:非邮件格式、-2：频繁发送、-3：发送失败

    public function __construct() {
        
    }

    /*
     * 送信
     * 
     * $from  - 发送人nick
     * $to    - 收件人nick
     * $title - 信的标题（字符串）
     * $msg   - 信的内容（字符串）
     * $tos   - 收件人nick拼接的‘，’分割串
     * $paper - 信纸编号,默认调用1号信纸
     * 
     */

    //修改记录2014-11-17 删除形参$tos=''、$paper='1'，添加形参$l_type,$_l_state='0'
    function send($from, $to, $title, $msg, $l_type, $l_state = '0', $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        if ($l_type == 0) {
            $to = '*';
        }
        if (strlen($from) != 32) {
            $sql = "SELECT u_id FROM c_user WHERE u_nick = '$from'";
            $db->Query($sql);
            $from = $db->getResultCol();
        }
        if ($to != '*') {
            if (strlen($to) != 32) {
                $sql = "SELECT u_id FROM c_user WHERE u_nick = '$to'";
                $db->Query($sql);
                $to = $db->getResultCol();
            }
        }

        $vartab = array(
            'l_body' => $msg,
            'l_title' => $title,
            'l_to' => $to,
            'l_from' => $from,
            'l_ctime' => F::mytime(),
            'l_type' => $l_type, //信件类型 0 系统信；1 群发信；2 个人信
            'l_state' => $l_state  //信件状态 0 未读；1 已读
                //	'l_group' => 0,  //暂时不用
        );

        return $db->InsertRecord('c_letter', $vartab);
    }

    //收信
    function receive() {
        
    }

    //刷新信箱，将信池信件添加到信箱中
    //返回最新收到的信件数；
    //$flag 当前用户身份，只能收当前身份的群发信息；
    function takeMail($nick, $flag, $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        //读取信箱中当前用户的信件的最大信件lid
        $sql = "SELECT mb_lid,mb_letterlen FROM c_mailbox WHERE mb_nick = '$nick' ORDER BY mb_lid DESC LIMIT 1 ";
        $db->Query($sql);
        $ret = $db->getCurRecode(PDO::FETCH_ASSOC);
        //取信件id的最大值  以及 信池长度
        $maxlid = is_null($ret['mb_lid']) ? '0' : $ret['mb_lid'];
        $maxletterlen = is_null($ret['mb_letterlen']) ? '0' : $ret['mb_letterlen'];
        //将两个最大值比较，取最大的值。
        $max = ($maxlid > $maxletterlen) ? $maxlid : $maxletterlen;

        //取最新的信池长度
        $sql = "SELECT count(*) as len FROM c_letter";
        $db->Query($sql);
        $letterlen = $db->getResultCol();

        //在信池中查询信件id大于max  且 收件人nick为当前用户的  信件id
        /* $sql = "(SELECT l_id FROM c_letter WHERE (l_to = '$nick' or l_to in ('{$flag}','all')) and l_id>$max)
          UNION
          (SELECT l_id FROM c_letter WHERE l_to = 'mass' and l_tos LIKE '%,{$nick},%' and l_id>$max)
          "; */
        $sql = "(SELECT l_id FROM c_letter WHERE (l_to = '$nick' or l_to = 'mass' or l_tos LIKE '%,{$nick},%' or l_to in ('{$flag}','all')) and l_id>$max)";

        $db->Query($sql);
        $rowid = $db->getAllRecodes();
        //$rowid为空，则说明没有新信件，返回0；
        if (count($rowid) == 0) {
            return 0;
        }
        //更新信箱
        //$rowid不为空，则组装参数数组；
        foreach ($rowid as $key => $val) {
            $tableval[$key] = array($nick, $rowid[$key][0], F::mytime(), $letterlen);
        }
        $varfildes = array('mb_nick', 'mb_lid', 'mb_ctime', 'mb_letterlen');
        return $db->InsertRecordsEx('c_mailbox', $varfildes, $tableval);
    }

    //读信，读取指定的信件信息
    function readLetterByMbid($mbid, $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "SELECT * FROM v_mailbox WHERE mb_id = $mbid";
        $db->Query($sql);
        return $db->getAllRecodes(PDO::FETCH_ASSOC);
    }

    //将信件标记成已读
    function isReadByMbid($mbid, $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "UPDATE c_mailbox SET mb_state = '1' WHERE mb_id = $mbid";
        return $db->Execute($sql);
    }

    //返回当前用户的所有信件
    function getLetterListByNick() {
        
    }

    //读信，读取指定的信件信息
    function readLetterByMblid($mblid, $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "SELECT * FROM v_mailbox WHERE mb_lid = $mblid";
        $db->Query($sql);
        return $db->getAllRecodes(PDO::FETCH_ASSOC);
    }

    //返回未读信息数量
    //当 mb_state == 0 时，表示该信件未读；
    function getNewMailNumByNick($nick = '', $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        $nick = is_null($nick) ? '' : "and mb_nick = '{$nick}'";
        $sql = "SELECT count(*) FROM c_mailbox WHERE mb_state = 0 $nick";
        $db->Query($sql);
        return $db->getResultCol();
    }

    //发送邮件 ---------------
    function mailto($address, $subject = 'title', $body) {
        date_default_timezone_set('PRC');
        include_once(FRAMEROOT."/lib/phpmailer/class.phpmailer.php");
        $mail = new PHPMailer(); // defaults to using php "mail()"
        $mail->IsSMTP();
        $mail->Host = "smtp.163.com";   // SMTP 服务器
        $mail->SMTPAuth = true;              // 打开SMTP 认证
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->Username = "13631261142@163.com";  // 用户名
        $mail->Password = "abc123456";          // 密码
//        $mail->AddReplyTo("admin@yikuaiyou.com", "admin");
        $mail->SetFrom('13631261142@163.com', '优品试用系统邮件');
//        $mail->AddReplyTo("admin@yikuaiyou.com", "admin");
        $mail->AddAddress($address, '优品试用会员');
        $mail->Subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $mail->IsHTML(true);
        $mail->CharSet = "utf-8";
        $mail->Encoding = "base64";
        // optional, comment out and test
        $mail->AltBody = $body;
        $mail->MsgHTML($body);
        if (!$mail->Send()) {
            return message::getJsonMsgStruct('0022', $mail->ErrorInfo);
        } else {
            return message::getJsonMsgStruct('1032');
        }
    }
    //发送邮件内容
    function sendCode($email,$nick = '大唐天下邮箱验证码', $tempId = 0,$content="",$time = 0){
        if (!F::isEmail($email)) {
            $this->l_errNum = -1;
            return false;
        }

        //防止频繁发送，间隔需要120秒
        if (!isset($_SESSION['posttime'])) {
            $_SESSION['posttime'] = time();
        } /* elseif (time() - $_SESSION['posttime'] < 30) {
            $this->l_errNum = -2;
            return false;
        } */ else {
            $_SESSION['posttime'] = time();
        }

        if (!isset($_SESSION['_mymailcode'])) {
            $code = rand(100000, 999999);
        } else {
            $code = $_SESSION['_mymailcode']['code'];
        }
		
		$body = $this->mailBody($tempId,$code,$content,$time);
        
        if ($this->sendmail(array('to'=>$email,'subject'=>$nick,'body'=>$body))) {
            $_SESSION['_mymailcode'] = array(
                'code' => $code,
                'email' => $email
            );
            log::writeLogMongo(19990011, 'email', $email, array('to'=>$email,'subject'=>$nick,'body'=>$body));
            if($tempId == 2){
                return $code;
            }else{
                return true;
            }
        } else {
            $this->l_errNum = -3;
			log::writeLogMongo(19990011, 'email', $email, array('error'=>1));
            return false;
        }
    }
    //邮箱发送的语句
    function mailBody($tempId,$code,$content,$time){
        switch ($tempId){
            case 0:
                $body = '<div style="font-size:14px;font-weight: bold;">亲爱的用户：'.$_SESSION['userNick'].'</div>
                        <div style="font-size:14px;margin-left:20px;font-weight: bold;">您好！</div>
                                <h3 style="margin-left:40px;">您的验证码为：<span style="color: #f60;font-size: 24px;">'.$code.'</span></h3>
                
                <p style="font-size:14px;margin-top: 15px;">感谢您的支持与合作！</p>
                <p style="font-size:14px;text-align: right;margin-top: 15px;max-width:320px;">【大唐天下商务大系统】</p>';
                break;
            case 1:
                $body = '<div style="font-size:14px;font-weight: bold;">亲爱的用户：</div>
                        <div style="font-size:14px;margin-left:20px;font-weight: bold;">您好！</div>
                                <h3 style="margin-left:40px;">您的验证码为：<span style="color: #f60;font-size: 24px;">'.$code.'</span></h3>
            
                <p style="font-size:14px;margin-top: 15px;">感谢您的支持与合作！</p>
                <p style="font-size:14px;text-align: right;margin-top: 15px;max-width:320px;">【大唐天下商务大系统】</p>';
                break;
            case 2:
                $body = '<div style="font-size:14px;font-weight: bold;">亲爱的会员：</div>
                         <div style="font-size:14px;margin-left:20px;font-weight: bold;">您好！</div>
                         <h3 style="margin-left:40px;">您的密码已重置为：<span style="color: #f60;font-size: 24px;">'.$code.'</span>。有疑问请联系客服。</h3>
                         <p style="font-size:14px;margin-top: 15px;">感谢您的支持与合作！</p>
                         <p style="font-size:14px;text-align: right;margin-top: 15px;max-width:320px;">【大唐天下商务大系统】</p>';
                break;
            case 3:
                $body = '<div style="font-size:14px;font-weight: bold;">尊敬的用户：</div>
                         <div style="font-size:14px;margin-left:20px;font-weight: bold;">您好！您于'.$time.'反馈的意见，我们已进行了处理。</div>
                         <p style="font-size:14px;margin-top: 15px;">处理结果如下：</p>
                         <h3 style="margin-left:40px;">'.$content.'</h3>                         
                         <p style="font-size:14px;margin-top: 15px;">本邮件由大唐天下系统自动发出，请勿直接回复！如仍有疑问请再次反馈或者联系客服。</p>
                         <p style="font-size:14px;margin-top: 15px;">（客服电话/客服QQ：400-8601-999；400-6632-999），感谢您的支持。</p>						 
                         <p style="font-size:14px;text-align: right;margin-top: 15px;max-width:320px;">【大唐天下商务大系统】</p>';
               break;
        }
        return $body;
    }
    
    //发送动作
    function sendmail($param=array()){

        $cfg['smtp']='mail.999qf.cn';
        $cfg['port']=25;
        $cfg['username']='service@999qf.cn';
        $cfg['password']='587412369';
        $cfg['email']='service@999qf.cn';
/*         $cfg['smtp']='smtp.163.com';
        $cfg['port']=25;
        $cfg['username']='13631261142@163.com';
        $cfg['password']='abc123456';
        $cfg['email']='13631261142@163.com'; */

        //使用该函数前请先加载PHPMAILER类
        $host=$cfg['smtp'];
        $port=$cfg['port'];
        $user=$cfg['username'];
        $password=$cfg['password'];
        $readto='';
        $from=$cfg['email'];
        $from_name='大唐天下';
        $to=$param['to'];
        $to_name='aas';
        $subject=$param['subject'];
        $body=$param['body'];
        $att='';
        $reto='';
        $reto_name='';
        $cc='';
        $bcc='';
        $charset='utf-8';

        include_once(FRAMEROOT."/lib/phpmailer/class.phpmailer.php");
        // Vendor('PHPMailer.class#phpmailer');
        $mail = new \PHPMailer();
//		print_r($param);

        //print_r($mail);exit;
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
        if (!isset($_SESSION['_mymailcode'])) {
            return false;
        } elseif ($_SESSION['_mymailcode']['code'] == $code && $_SESSION['_mymailcode']['email'] == $email) {
            unset($_SESSION['_mymailcode']);
            log::writelog('用户手机' . $email . '验证了验证码' . $code . '，验证成功。', 'sms');
            return true;
        } else {
            log::writelog('用户手机' . $email . '验证了验证码' . $code . '，验证失败。', 'sms');
            return false;
        }
    }

    //获取错误代码
    public function getErrNum(){
        return $this->l_errNum;
    }

}
