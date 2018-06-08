<?php
/*=============================================================================
#     FileName: editCourse.php
#         Desc: 修改课程
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:46:13
#      History:
#      Paramer:
=============================================================================*/
class editCourse extends worker {
    function __construct($options) {
        parent::__construct($options, [500104]);
    }

    function run() {
        $options = $this->options;
        $db      = new MySql();

        $data = array(
            'code' => '500104',
            'tempId'   => 'temp_'.F::getGID(),
        );

        $info = $db->getRow("SELECT * FROM tang_course WHERE co_id='{$options['coID']}'");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','获取课程信息失败')));
        }

        $info['logoUrl'] = !empty($info['co_logo']) ? TFS_APIURL.'/'.$info['co_logo'] : _TEMP_PUBLIC_."/images/none.png";
        $studyDirection = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE sd_state=0');
        $studyDirection = array_column($studyDirection,'sd_name','sd_id');

        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $data['studyDirection'] = F::array2Options($studyDirection,array($info['co_studyDirectionId']));
        $data['grade']          = F::array2Options($grade,array($info['co_gradeID']));
        $data['stateList']      = F::array2Options(array(-1=>'失效',1=>'有效'),array($info['co_state']));
        $data['jsData']         = json_encode($info);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
