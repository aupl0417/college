<?php
/*=============================================================================
#     FileName: modCourseSchedule.php
#         Desc: 临时调课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:49:36
#      History:
#      Paramer:
=============================================================================*/
class modCourseSchedule extends worker {
    function __construct($options) {
        parent::__construct($options, [500102]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die('<strong class="font-red">参数错误</strong>');
        }

        $db = new MySql();
        $sql = 'SELECT cl.cl_name,co.co_name,cta.cta_trainingsiteId,cta.cta_id,cta.cta_teacherId FROM tang_class_table cta
            LEFT JOIN tang_class cl ON cl.cl_id=cta.cta_classId LEFT JOIN tang_course co ON co.co_id=cta.cta_courseId'
            ." WHERE cta.cta_id={$options['id']}";
        $info = $db->getRow($sql);

        $info['trainingsite'] = $db->getAll('SELECT tra_id,tra_name FROM tang_trainingsite');
        $sql = "SELECT id,truename FROM tang_ucenter_member WHERE identityType=1 ORDER BY CONVERT(username USING gbk)";
        $info['teacherList'] = $db->getAll($sql);

        $data = array(
            'code'   => '500102',
            'tempId' => 'temp_'.F::getGID(),
            'jsData' => json_encode($info),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
