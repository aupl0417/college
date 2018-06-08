<?php

class editOrg_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60122]);			
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        $org = isset($this->options['org'])?$this->options['org']:"";
        if ($id == "" || $org==""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldOrg = $db->getField("select u_comOrgCode from t_user where u_id='".$id."'");
        if(!$user->uniqueUserInfo(9, $org)){
            $this->show(message::getJsonMsgStruct('1002','组织机构代码证被占用或证件号错误'));
            exit;
        }
        $data = array(
            'u_comOrgCode'  => $org,
        );
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改用户组织机构代码证操作';
                log::writeLogMongo(60122, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 18,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldOrg,
                    'ut_newValue' => $org,
                    'ut_reason'   =>'修改用户组织机构代码证操作',
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
