<?php

class editTaxCode_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60127]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        $code = isset($this->options['code'])?$this->options['code']:"";
        if ($id == "" || $code == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        if(!$user->uniqueUserInfo(10, $code)){
            $this->show(message::getJsonMsgStruct('2010'));//营业执照错误
            exit;
        }
        $oldCode = $db->getField("select u_comTaxCode from t_user where u_id='".$id."'");
        $data = array(
            'u_comTaxCode'  => $code,
        );
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改企业税务登记证';
                log::writeLogMongo(60127, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 21,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldCode,
                    'ut_newValue' => $code,
                    'ut_reason'   =>'修改企业税务登记证',
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
