<?php
/*=============================================================================
#     FileName: classAppearance.json.php
#         Desc: 学员风采
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-26 15:45:10
#      History:
#      Paramer:
=============================================================================*/

class classAppearance_json extends api {
    function run() {
        $options = $this->options;
        $db      = new MySql();
        $where   = ' WHERE cl_state=1';

        if (isset($options['classId']) && !empty($options['classId'])) {
            $where = " AND cl_id='{$options['classId']}'";
        }

        $classInfo = $db->getAll("SELECT cl_id,cl_name,cl_startTime,cl_endTime FROM tang_class $where");

        if (empty($classInfo)) {
            return apis::apiCallback('1002', '获取班级信息失败');
        }

        $classIdList = array_column($classInfo,'cl_id');
        $classIdList = implode(',',$classIdList);

        $sql = "SELECT tcp_classId,tcp_filename,tcp_title,tcp_isLogo FROM tang_class_picture WHERE tcp_classId In($classIdList)";
        $classPictures = $db->getAll($sql);

        if (empty($classPictures)) {
            return apis::apiCallback('1001',$classInfo);
        }

        foreach ($classInfo as &$v) {
            $v['classPictures'] = [];
            foreach ($classPictures as $p) {
                if ($v['cl_id'] == $p['tcp_classId']) {
                    $p['filename'] = TFS_APIURL.'/'.$p['tcp_filename'];
                    $v['classPictures'][] = $p;
                }
            }
        }

        return apis::apiCallback('1001',$classInfo);
    }
}
