<?php
/*=============================================================================
#     FileName: viewEnroll.php
#         Desc: 发布报名记录
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:44:59
#      History:
#      Paramer:
=============================================================================*/
class viewEnroll extends worker {
    function __construct($options) {
        parent::__construct($options, [50010301]);
    }

    function run() {
        $data = array(
            'code'   => '50010301',
            'tempId' => 'temp_' . F::getGID()
        );

        $options = $this->options;
        if (!isset($options['id']) || empty($options['id'])) {
            die('<span class="font-red">参数错误</span>');
        }

        $db = new MySql();
        $sql = "SELECT cl.*,br_name,um.trueName FROM tang_class cl LEFT JOIN tang_branch br ON br.br_id=cl.tangCollege
            LEFT JOIN tang_ucenter_member um ON cl.cl_headmasterId=um.id
            WHERE cl_id='{$options['id']}'";
        $info = $db->getRow($sql);

        $levelCondition = F::getAttrs(9);
        $isNot          = array('否','是');

        //报名条件
        $enrollCondition        = unserialize($info['cl_enrollCondition']);
        $info['levelCondition'] = $levelCondition[$enrollCondition['levelCondition']];
        $info['isAuthed']       = $isNot[$enrollCondition['isAuthed']];
        $info['enrollEver']     = $isNot[$enrollCondition['enrollEver']];
        $info['isBlack']        = $isNot[$enrollCondition['isBlack']];

        $sql = "SELECT at_value FROM tang_attrib WHERE at_key='%d' AND at_type='%d'";

        $info['hostel']   = $db->getField(sprintf($sql,$info['cl_hostel'],2));
        $info['catering'] = $db->getField(sprintf($sql,$info['cl_catering'],1));

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
