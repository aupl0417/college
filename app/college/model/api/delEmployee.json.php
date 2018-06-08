<?php
/*=============================================================================
#     FileName: delEmployee.json.php
#         Desc: 删除雇员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-26 11:25:56
#      History:
#      Paramer: 
=============================================================================*/

class delEmployee_json extends api {
    function run() {
        $options = $this->options;

        if (!isset($options['e_id']) || empty($options['e_id'])) {
            return apis::apiCallback('1002', '参数错误，雇员id不能为空'); 	
        }

        $db = new MySql();
        $isExist = $db->count('tang_employee',"e_id='{$options['e_id']}'");

        if (!$isExist) {
            return apis::apiCallback('1001','删除雇员信息成功'); 	
        }

        $delRes = $db->delete('tang_employee',"e_id='{$options['e_id']}'");

        if(1 != $delRes){
            return apis::apiCallback('1002','删除雇员信息错误'); 	
        }

        return apis::apiCallback('1001','删除雇员信息成功'); 	
    }
}
