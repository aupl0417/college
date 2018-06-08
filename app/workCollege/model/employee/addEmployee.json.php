<?php

class addEmployee_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [500501]);
    }

    function run() {	
        $data = $this->options;
        $num = isset($data['num']) ? $data['num'] : '';
        if($num == ''){
            $this->show(message::getJsonMsgStruct('1002', '工号错误'));//
            exit;
        }
        $db = new MySql();
        $result = $db->getAll("select e_id from t_employee");
        if ($result){
            foreach ($result as $key => $val){
                if ($val['e_id'] == $num){
                    $this->show(message::getJsonMsgStruct('1002', '工号重复'));//区域格式为空
                    exit;
                }
            }
        }
        $charName = isset($data['charName'])?$data['charName']:"";
        if (strlen($charName) >20){
            $this->show(message::getJsonMsgStruct('1002','姓名拼音不能超过二十位'));
            exit;
        }
        $name = isset($data['name'])?$data['name']:"";
        $tel = isset($data['tel'])?$data['tel']:"";
//         if (isMaxLength($tel,11)){
            
//         }
        
        $card = isset($data['card'])? $data['card'] : '';
        $org = isset($data['org'])? $data['org'] : 0;
        if ($org == "" || $org ==null){
            $orgId = 0;
        }else{
            $orgId = $db->getField("select dm_id from t_organization where dm_code='".$org."'");
        }
        $duty = isset($data['duty'])? $data['duty'] : '';
        $arr = array(
            'e_id' 			=> $num,
            'e_uid'         => F::getGID(32),
            'e_name' 	    => $name,
            'e_tel' 		=> $tel,
            'e_certNum' 	=> $card,
            'e_departmentID'=> $orgId,
            'e_dutyID'	    => $duty,
            'e_loginPwd'    => F::getSuperMD5("123456"),
            'e_createTime'  => F::mytime(),
            'e_joinTime'    => F::mytime(),
            'e_state'       => 1,
            'e_charName'    => $charName,
            'e_logIp'       => F::GetIP(),
        ); 
        $result = $db->insert("t_employee",$arr);
        //记录操作日志
        $arr['memo'] = '添加雇员';//尽量写得详细一点点了
        log::writeLogMongo(20205, 't_employee', $result, $arr);

        if($result = 1){
            $this->show(message::getJsonMsgStruct('1001','修改成功'));
        }else{
            $this->show(message::getJsonMsgStruct('1002','修改失败，没有更新'));
        }
	}
}
