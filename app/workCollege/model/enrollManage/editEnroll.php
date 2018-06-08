<?php
/*=============================================================================
#     FileName: editEnroll.php
#         Desc: 编辑报名信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-12-20 10:51:08
#      History:
#      Paramer:
=============================================================================*/
class editEnroll extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;
        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db             = new MySql();
        $studyDirection = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE 1');
        $studyDirection = array_column($studyDirection,'sd_name','sd_id');

        $courseTemplate = $db->getAll('SELECT ctt_id,ctt_name FROM tang_class_table_templet WHERE ctt_state=1');
        $courseTemplate = array_column($courseTemplate,'ctt_name','ctt_id');

        $catering = F::getAttrs(1);
        $hostel   = F::getAttrs(2);

        $branch = $db->getAll('SELECT br_id,br_name FROM tang_branch where br_state=1');
        $branch = array_column($branch,'br_name','br_id');

        $headmaster = $db->getAll('SELECT id,trueName FROM tang_ucenter_member WHERE identityType=1');
        $headmaster = array_column($headmaster,'trueName','id');

        $info = $db->getRow("SELECT * FROM tang_class WHERE cl_id={$options['id']}");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','获取报名信息错误')));
        }

        $info['logoUrl'] = !empty($info['cl_logo']) ? TFS_APIURL.'/'.$info['cl_logo'] : _TEMP_PUBLIC_."/images/none.png";

        $classCourseList = $db->getAll("SELECT * FROM tang_class_course WHERE cc_classId='{$info['cl_id']}'");
        $tempCourseList = $db->getAll("SELECT co_id,co_name FROM tang_course WHERE co_state=1");
        $tempCourseList = array_column($tempCourseList,'co_name','co_id');

        $info['enrollCondition'] = unserialize($info['cl_enrollCondition']);

        foreach ($classCourseList as $v) {
            $course[] = array(
                'course' => $tempCourseList,
                'co_id'  => $v['cc_courseId'],
                'hour'   => $v['cc_hour'].' 课时',
                'credit' => $v['cc_credit'].' 学分',
            );
        }

        $condition = $db->getAll('SELECT stc_id,stc_name FROM tang_study_condition WHERE stc_state=1');
        $classCondition = explode(',',$info['cl_condition']);

        unset($v);
        foreach ($condition as &$v) {
            $v['checked'] = in_array($v['stc_id'],$classCondition) ? 'checked' : '';
        }

        $jsData = array(
            'condition'      => $condition,
            'studyDirection' => $studyDirection,
            'courseTemplate' => $courseTemplate,
            'course'         => $course,
            'catering'       => $catering,
            'hostel'         => $hostel,
            'branch'         => $branch,
            'headmaster'     => $headmaster,
            'courseList'     => $course,
            'info'           => $info,
            'stateList'      => array('-1'=>'审核不通过','待审核','审核通过'),
        );

        $data = array(
            'code'   => '500105',
            'jsData' => json_encode($jsData),
        );


        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
