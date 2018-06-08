<?php
/*=============================================================================
#     FileName: viewClass.php
#         Desc: 班级详情
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:12
#      History:
#      Paramer:
=============================================================================*/
class viewClass extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $data = array(
            'code'   => '500103',
            'tempId' => 'temp_' . F::getGID()
        );

        $options = $this->options;
        if (!isset($options['clID']) || empty($options['clID'])) {
            die('<span class="font-red">参数错误</span>');
        }

        $db = new MySql();

        $sql = "SELECT * FROM tang_class WHERE cl_id='{$options['clID']}'";

        $info = $db->getRow($sql);

        if (empty($info)) {
            die('<span class="font-red">获取信息失败</span>');
        }

        if ($info['cl_condition']) {
            $studyDirection = $db->getAll("SELECT stc_name FROM tang_study_condition WHERE stc_id IN({$info['cl_condition']})");
            $info['condition'] = join(' | ',array_column($studyDirection,'stc_name'));
        }

        $info['cl_logo'] = !empty($info['cl_logo']) ? TFS_APIURL.'/'.$info['cl_logo'] : _TEMP_PUBLIC_."/images/none.png";

        $classCourseList = $db->getAll("SELECT * FROM tang_class_course WHERE cc_classId='{$info['cl_id']}'");
        $tempCourseList = $db->getAll("SELECT co_id,co_name FROM tang_course WHERE co_state=1");
        $tempCourseList = array_column($tempCourseList,'co_name','co_id');

        foreach ($classCourseList as $v) {
            $course[] = array(
                'courseName' => $tempCourseList[$v['cc_courseId']],
                'hour'       => $v['cc_hour'],
                'credit'     => $v['cc_credit'],
            );
        }

        $info['course'] = $course;
        $data['jsData'] = json_encode($info);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
