<?php

class editLegal_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60120]);			
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        $legal = isset($this->options['legal'])?$this->options['legal']:"";
        if ($id == "" || $legal==""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $odlLegal = $db->getField("select u_comLegalName from t_user where u_id='".$id."'");
        $data = array(
            'u_comLegalName'  => $legal,
        );
        
        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改企业法人操作';
                log::writeLogMongo(60120, 't_user', $id, $data);
        
                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 16,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $odlLegal,
                    'ut_newValue' => $legal,
                    'ut_reason'   =>'修改会员法人',
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
