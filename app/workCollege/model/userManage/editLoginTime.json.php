<?php

class editLoginTime_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60130]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        $registerTime = isset($this->options['loginTime'])?$this->options['loginTime']:"";
        if ($id == "" || $registerTime == "" || !F::verifyDateTime($registerTime)){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldTime = $db->getRow ("select u_createTime,u_upgradeTime,u_nick from t_user where u_id='".$id."'");
        if (strtotime($registerTime) > strtotime($oldTime['u_upgradeTime'])){
            $this->show(message::getJsonMsgStruct('1002','注册时间不能在升级时间的后面'));
            exit;
        }
//         $info = $db->getRow("SELECT u_nick AS 'ID', u_createTime AS 'loginTime', u_upgradeTime AS 'upTime',
// 				(SELECT u_nick FROM t_user WHERE u_code<u1.u_code ORDER BY u_code DESC LIMIT 1) AS 'preID',
// 				(SELECT u_createTime FROM t_user WHERE u_code<u1.u_code ORDER BY u_code DESC LIMIT 1) AS 'preregisterTime',
// 				(SELECT u_nick FROM t_user WHERE u_code>u1.u_code ORDER BY u_code ASC LIMIT 1) AS 'nextID',
// 				(SELECT u_createTime FROM t_user WHERE u_code>u1.u_code ORDER BY u_code ASC LIMIT 1) AS 'nextregisterTime'
// 				FROM
// 				t_user AS u1
// 				WHERE u_nick='".$oldTime['u_nick']."'");
//         if (strtotime($info['preregisterTime']) > strtotime($registerTime)){
//             $this->show(message::getJsonMsgStruct('1002','注册时间不能在上一个注册会员的前面'));
//             exit;
//         }
//         if (strtotime($info['nextregisterTime']) < strtotime($registerTime)){
//             $this->show(message::getJsonMsgStruct('1002','注册时间不能在下一个注册会员的后面'));
//             exit;
//         }
        $data = array(
            'u_createTime'  => $registerTime,
        );
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改会员的注册时间操作';
                log::writeLogMongo(60130, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 25,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldTime['u_createTime'],
                    'ut_newValue' => $registerTime,
                    'ut_reason'   =>'修改注册时间',
                );
                $resNk = $db->insert("t_user_tran", $userTran);
                if(!$resNk){
                    throw new Exception('-1');
                }
                $db->commitTRAN();
                $this->show(message::getJsonMsgStruct('1001', "修改成功！"));//成功
            }else{
                throw new Exception('-1');
            }
        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}
