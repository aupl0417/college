<?php

class editLoginTime extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60130]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = $db->getRow("select u_createTime,u_upgradeTime,u_nick from t_user where u_id='".$id."'");
        if ($user['u_createTime'] <= $user['u_upgradeTime']){
            $this->show(message::getJsonMsgStruct('1002','时间正确，无需修改！'));
            exit;
        }
        $info = $db->getRow("SELECT u_nick AS 'ID', u_createTime AS 'loginTime', u_upgradeTime AS 'upTime',
				(SELECT u_nick FROM t_user WHERE u_code<u1.u_code ORDER BY u_code DESC LIMIT 1) AS 'preID',
				(SELECT u_createTime FROM t_user WHERE u_code<u1.u_code ORDER BY u_code DESC LIMIT 1) AS 'preloginTime', 
				(SELECT u_nick FROM t_user WHERE u_code>u1.u_code ORDER BY u_code ASC LIMIT 1) AS 'nextID',
				(SELECT u_createTime FROM t_user WHERE u_code>u1.u_code ORDER BY u_code ASC LIMIT 1) AS 'nextloginTime'
				FROM 
				t_user AS u1
				WHERE u_nick='".$user['u_nick']."'");
        $pre = "用户名:".$info['preID'].",创建时间:".$info['preloginTime'];
        $next = "用户名:".$info['nextID'].",创建时间:".$info['nextloginTime'];
        $data = array(
            'id'       => $id,
            'pre'      => $pre,
            'next'     => $next,
            'upTime'   => $user['u_upgradeTime'],
            'loginTime'=> $user['u_createTime'],
//             'editTime' => $user['preloginTime'],
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
