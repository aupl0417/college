<?php
/*=============================================================================
#     FileName: addEmployee.json.php
#         Desc: 添加雇员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-26 10:10:36
#      History:
#      Paramer: 
=============================================================================*/

class addEmployee_json extends api {
    function run() {
        $options = $this->options;

        if (empty($options)) {
            return apis::apiCallback('1002', '雇员信息错误'); 	
        }

        $employeeInfo = $options;
        $defaultPower = '50,5001,50101,50010101';
        $employeeInfo['e_powerList']  = $defaultPower;
        $employeeInfo['e_powerHash']  = F::powerHash($defaultPower);

        $employeeInfo = array_diff_key($employeeInfo,array('PATH_ACTION'=>'','PATH_MODEL'=>''));

        $db = new MySql();

        $isExist = $db->count('tang_employee',"e_id='{$employeeInfo['e_id']}'");

        if ($isExist) {
            $res = $db->update('tang_employee',$employeeInfo,"e_id='{$employeeInfo['e_id']}'");
            if(false === $res){
                return apis::apiCallback('1002', '雇员信息修改或添加错误'); 	
            }
        }else{
            $res = $db->insert('tang_employee',$employeeInfo);
            if(1 != $res){
                return apis::apiCallback('1002', '雇员信息修改或添加错误'); 	
            }
        }

        return apis::apiCallback('1001','雇员信息修改或添加成功'); 	
    }
}
