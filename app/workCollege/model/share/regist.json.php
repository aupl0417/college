<?php

//登录的模块类。
class regist_json extends guest {

    function run() {
        //校验nick
        $nick = $this->options['nick'];
        if (is_numeric($nick)) {
            return $this->show(message::getJsonMsgStruct('1097'));
        }
        if (preg_match("/^(yp|YP|Yp|yP)\d*$/", $nick)) {
           return $this->show(message::getJsonMsgStruct('0045'));
        }
        if (strlen($nick) < 8) {
           return $this->show(message::getJsonMsgStruct('0047'));
        }
        //校验密码
        $pwd = $this->options['pwd'];
        $rpwd = $this->options['rpwd'];
        if ($pwd !== $rpwd) {
           return $this->show(message::getJsonMsgStruct('0046'));
        }
        //用户类型
        $usertype = 1; //由于系统不严格区分角色，所以用户类型默认为1
        //校验邮箱
        $email = $this->options['email'];
        if (!F::isEmail($email)) {
           return $this->show(message::getJsonMsgStruct('1084'));
        }
        /* 限制注册频率，同一个ip60分钟只能注册成功一次 ,防止机器人批量注册 */
        /*
          暂时关闭 by flybug
          $cache = new cache();
          $prefix = 'ip_repeat_check';
          $ip = F::GetIP();
          if ($cache->get(md5($prefix . $ip))) {
          return message::getJsonMsgStruct('10049');
          } */
        //检测邮箱是否注册
        $us = new users();
        $arr = $us->getUserByEMAIL($email, "u_id");
        $arrnum = count($arr);
        if (( $arrnum > 0)) {
            return $this->show(message::getJsonMsgStruct('1035'));
        }
        //推荐人
        //校对推荐人
        $fuser = $us->getUserByCode($this->options['exid']);
        $fusernum = count($fuser);
        if ($fusernum == 0) {
            $fcode = '0';
            $addfcode = 0;
        } else {
            $fuserid = $fuser['u_id'];
            $addfcode = 1;
            $fcode = $this->options['exid'];
            $sg_code = 10;
        }
        //校验手机是否被注册
        $phone = isset($this->options['phone']) ? $this->options['phone'] : '';
        if (!F::isPhone($phone)) {
            die(message::getJsonMsgStruct('0048'));
        }
        $db = new MySql();
        $sql = "SELECT COUNT(*) FROM c_user WHERE u_tel = '$phone'";
        $db->Query($sql);
        if ($db->getResultCol() != 0) {
            die(message::getJsonMsgStruct('0052'));
        }
        $sms = new sms();
        if (!$sms->TestValidateByIndex($this->options['token'], $this->options['phone'], $this->options['code'])) {
            die(message::getJsonMsgStruct('0051'));
        }
        //获得用户id
        $time = F::mytime();
        $uid = $us->getNewUserID();
        $arr = array(
            'u_id' => $uid,
            'u_nick' => $nick,
            'u_pwd' => F::getMD5($rpwd),
            'u_type' => $usertype,
            'u_email' => $email,
            'u_tel' => $this->options['phone'],
            'u_logtime' => $time,
            'u_ctime' => $time,
            'u_chtime' => $time,
            'u_fcode' => $fcode,
                //防止机器人批量注册，所有新注册注册成功后强制再手机认证
                //                   'u_auth' => '2,',
                //                   'u_blevel' => 1
        );

        //控制推荐人积分的增加
        //通过推荐人的推荐码，查询24小时内推荐的人数；
        //24小时的取法：取当前时间的年月日，时间归零，即当前天内时间；
        //当前时间
        $arrtime = getdate();

        $newtime = $arrtime['year'] . '-' . $arrtime['mon'] . '-' . $arrtime['mday'] . ' 00:00:00';

        $sql = "SELECT u_id FROM c_user WHERE u_ctime > '{$newtime}' AND u_fcode = '{$fcode}'";

        $db->Query($sql);
        $fcodenum = $db->getAllRecodes(PDO::FETCH_ASSOC);
        //今天本code推荐的人数
        $tjnum = count($fcodenum);
        $ac = new account();
        $db->BeginTRAN();
        try {
            //创建用户
            if ($us->createUser($arr, $db) != 1) {
                throw new Exception('创建用户失败', -1);
            }
            //创建账户
            if ($ac->createAccount($uid, $usertype, $db) != 1) {
                throw new Exception('创建账户失败', -1);
            }
            //注册就送20积分
            if (!$ac->operatScore('add', $uid, 20, '注册就送20积分' . $nick, $db)) {
                throw new Exception('创建日志失败', -1);
            }
            //为推荐人加积分
            if ($tjnum < 30) {
                if ($addfcode == 1) {
                    //增加账户积分
                    if (!$ac->operatScore('add', $fuserid, $sg_code, '推荐用户' . $nick, $db)) {
                        throw new Exception('创建日志失败', -1);
                    }
                }
            }
            //创建下单表
            $db->insert('stat_user', array('uid' => $uid));
            //创建user扩展表记录
            $db->insert('c_userex', array('ue_id' => $uid, 'ue_chtime' => F::mytime()));
            $db->CommitTRAN();
            //将用户ip存入memecache
            $cache->set(md5($prefix . $ip), 1, 3600);
            return message::getJsonMsgStruct('1004'); //返回‘注册用户成功’
        } catch (Exception $e) {
            $db->RollBackTRAN();
            return message::getJsonMsgStruct('1005'); //返回'注册用户失败'
        }
    }

}
