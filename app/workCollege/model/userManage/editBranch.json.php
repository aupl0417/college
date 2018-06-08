<?php

class editBranch_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60124]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        $branch = isset($this->options['branch'])?$this->options['branch']:"";
        if ($id == "" || $branch == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldBranch = $db->getField("select u_isBranch from t_user where u_id='".$id."'");
        $data = array(
            'u_isBranch'  => $branch,
        );
        if ($oldBranch){
            $oldBranch = "是";
        }else{
            $oldBranch = "否";
        }
        if($branch){
            $branch = "是";
        }else{
            $branch = "否";
        }
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改是否为分公司操作';
                log::writeLogMongo(60124, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 20,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldBranch,
                    'ut_newValue' => $branch,
                    'ut_reason'   =>'修改是否为分公司',
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
