<?php
/*=============================================================================
#     FileName: editCourseTemplate.php
#         Desc: 修改课程模板
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:46:19
#      History:
#      Paramer:
=============================================================================*/
class editCourseTemplate extends worker {
    function __construct($options) {
        parent::__construct($options, [50010402]);
    }

    function run() {
        $options = $this->options;
        $db      = new MySql();

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show('<strong class="font-red">参数错误</strong>'));
        }

        $studyDirection = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE 1');
        $studyDirection = array_column($studyDirection,'sd_name','sd_id');

        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $data = array(
            'code'   => '50010402',
            'tempId' => 'temp_'.F::getGID(),
            'studyDirection' => F::array2Options($studyDirection),
        );


        $info = $db->getRow("SELECT * FROM tang_class_table_templet WHERE ctt_id='{$options['id']}'");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','获取模板信息失败')));
        }

        $info['logoUrl'] = !empty($info['ctt_logo']) ? TFS_APIURL.'/'.$info['ctt_logo'] : _TEMP_PUBLIC_."/images/none.png";

        $info['grade'] = $grade;
        $data = array_merge($data,$info);

        $condition = $db->getAll('SELECT stc_id,stc_name FROM tang_study_condition WHERE stc_state=1');
        $infoConditon = explode(',',$info['ctt_condition']);
        $str = "<label> <input name='condition[]' type='checkbox' value='%s' %s/>%s</label>";

        foreach ($condition as $v) {
            $checked = in_array($v['stc_id'],$infoConditon) ? 'checked' : '';
            $conditionList[] = sprintf($str,$v['stc_id'],$checked,$v['stc_name']);
        }

        $data['condition'] = join('',$conditionList);

        $sql = "SELECT co_id,co_name,co_credit,co_hour FROM tang_course WHERE co_state=1 %s";
        $infoCourseList = $db->getAll(sprintf($sql," AND co_id IN({$info['ctt_course']})"));
        $courseList = $db->getAll(sprintf($sql,''));
        $courseList = array_column($courseList,'co_name','co_id');

        $info['stateList'] = array(-1=>'失效',1=>'有效');

        if (!empty($infoCourseList)) {
            unset($v);
            foreach ($infoCourseList as $v) {
                $course[] = array(
                    'course' => $courseList,
                    'co_id'  => $v['co_id'],
                    'hour'   => $v['co_hour'].' 课时',
                    'credit' => $v['co_credit'].' 学分',
                );
            }
        }

        //echo '<pre>';
        //print_r($info);exit;

        $data['jsData'] = json_encode(array('course'=>$course,'info'=>$info));

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
