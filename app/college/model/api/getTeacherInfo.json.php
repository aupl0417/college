<?php
/*=============================================================================
#     FileName: getTeacherInfo.json.php
#         Desc: 获取老师信息 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-22 16:09:05
#      History:
#      Paramer:
=============================================================================*/

class getTeacherInfo_json extends api{
    function run() {
        $options = $this->options;
        if (!isset($options['userId']) || empty($options['userId'])) {
            return apis::apiCallback('1002', '讲师ID参数错误');
        }

        //讲师资料
        $db = new MySql();
        $sql = "SELECT te_photo,te_userId,te_birthday,te_IDNum,te_workExperience,te_eduLevel,te_fromAcademy,te_description,te_level,
            te_teachGrade,te_sex,te_courseReward,tl_name levelName
            FROM tang_teacher te LEFT JOIN tang_teacher_level tl On te.te_level=tl.tl_id
            LEFT JOIN tang_ucenter_member um ON um.id=te.te_userId
            WHERE um.userId='{$options['userId']}'";
        $teacherInfo = $db->getRow($sql);

        if (empty($teacherInfo)) {
            return apis::apiCallback('1002', '获取讲师档案信息错误');
        }

        return apis::apiCallback('1001',$teacherInfo);
    }
}
