<?php

class editComType_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60123]);			
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        $companyType = isset($this->options['companyType'])?$this->options['companyType']:"";
        if ($id == "" || $companyType == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldType = $db->getField("select u_companyType from t_user where u_id='".$id."'");
        $data = array(
            'u_companyType'  => $companyType,
        );
        $companyTypes = F::getAttrs(4);
//         dump($oldType);die;
        if ($oldType){
            $oldType = $companyTypes[$oldType];
        }
        $companyType = $companyTypes[$companyType];
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改企业类型操作';
                log::writeLogMongo(60123, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 19,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldType,
                    'ut_newValue' => $companyType,
                    'ut_reason'   =>'修改企业类型',
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
