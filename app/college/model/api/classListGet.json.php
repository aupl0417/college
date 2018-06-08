<?php
/*=============================================================================
#     FileName: classListGet.json.php
#         Desc: 获取报名班级列表 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-07 10:38:25
#      History:
#      Paramer: 
=============================================================================*/

class classListGet_json extends api {
    function run() {

        $db = new MySql();
        $sql = 'SELECT cl.*,br_name FROM tang_class cl LEFT JOIN tang_branch br ON br.br_id=cl.tangCollege WHERE cl_isTest =0 AND cl_state IN(0,1,2) AND cl_status=1 ORDER BY cl_createTime DESC';

        $classList = $db->getAll($sql);

        if(empty($classList)){
            return apis::apiCallback('1001', []); 	
        }

        $classIdList = array_column($classList,'cl_id');
        $classIdList   = implode(',',$classIdList);

        $sql = "SELECT tse_classId,count(1) total FROM tang_student_enroll WHERE tse_classId IN($classIdList) AND tse_state !=-1 AND tse_status !=-1 GROUP BY tse_classId";
        $enrollNum = $db->getAll($sql);

        if (!empty($enrollNum)) {
            $enrollNum = array_column($enrollNum,'total','tse_classId');
        }

        foreach ($classList as &$v) {
            $v['cl_logo']   = TFS_APIURL.'/'.$v['cl_logo'];
            $v['enrollNum'] = intval($enrollNum[$v['cl_id']]);
        }

        return apis::apiCallback('1001', $classList); 	
    }
}
