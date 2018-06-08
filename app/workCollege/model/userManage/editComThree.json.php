<?php

class editComThree_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60125]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        $Three = isset($this->options['three'])?$this->options['three']:"";
        if ($id == "" || $Three == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldThree = $db->getField("select u_companyThree from t_user where u_id='".$id."'");
        $data = array(
            'u_companyThree'  => $Three,
        );
        if ($oldThree){
            $oldThree = "是";
        }else{
            $oldThree = "否";
        }
        if($Three){
            $Three = "是";
        }else{
            $Three = "否";
        }
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改企业三证合一操作';
                log::writeLogMongo(60125, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 21,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldThree,
                    'ut_newValue' => $Three,
                    'ut_reason'   =>'修改企业三证合一',
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
