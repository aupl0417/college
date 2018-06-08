<?php
/*=============================================================================
#     FileName: classInfoGet.json.php
#         Desc: 获取报名班级详情
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-06 15:43:53
#      History:
#      Paramer: 
=============================================================================*/

class classInfoGet_json extends api {
    function run() {

        $options = $this->options;
        $id      = intval($options['id']);
        $db      = new MySql();

        $sql = "SELECT cl.*,br_name FROM tang_class cl LEFT JOIN tang_branch br ON br.br_id=cl.tangCollege WHERE cl_id='{$options['id']}'";

        $info = $db->getRow($sql);

        if(empty($info)){
            return apis::apiCallback('1001', []); 	
        }

        $info['enrollNum'] = $db->count('tang_student_enroll',"tse_ClassId={$options['id']} AND tse_status IN (0,1)");
        $info['cl_address'] = $db->getField("SELECT tra_address FROM tang_trainingsite WHERE tra_id='{$info['cl_defaultTrainingsiteId']}'");

        return apis::apiCallback('1001', $info); 	
    }
}
