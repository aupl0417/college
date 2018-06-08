<?php
/*=============================================================================
#     FileName: delEnroll.json.php
#         Desc: 删除班级报名发布信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:44:20
#      History:
#      Paramer:
=============================================================================*/
class delEnroll_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();

        try{
            $db->beginTRAN();
            //$delEnrollRes = $db->delete('tang_class_temp'," cl_id='{$options['id']}'");
            $delEnrollRes = $db->update('tang_class',array('cl_status'=>-1)," cl_id='{$options['id']}'");

            if (1 != $delEnrollRes) {
                throw new Exception('删除报名信息失败','-1');
            }

            //$delCourseRes = $db->delete('tang_class_course'," cc_classId='{$options['id']}'");

            //if (1 > $delCourseRes) {
            //    throw new Exception('删除报名班级的课程信息失败','-2');
            //}

            $db->commitTRAN();
            die($this->show(message::getJsonMsgStruct(1001,'删除成功')));
        }catch(Exception $e){
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct(1002,$e->getMessage())));
        }
    }
}
