<?php
/*=============================================================================
#     FileName: viewCourse.php
#         Desc: 查看课程
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:45:59
#      History:
#      Paramer:
=============================================================================*/
class viewCourse extends worker {
    function __construct($options) {
        parent::__construct($options, [50010401]);
    }

    function run() {
        $options = $this->options;
        $db      = new MySql();

        $data = array(
            'code' => '50010401',
            'tempId'   => 'temp_'.F::getGID(),
        );

        $sql = "SELECT co.*,gr.gr_name,sd.sd_name FROM tang_course co LEFT JOIN tang_grade gr ON co.co_gradeID=gr.gr_id
            LEFT JOIN tang_study_direction sd ON co.co_studyDirectionId=sd.sd_id WHERE co_id='{$options['id']}'";

        $info = $db->getRow($sql);

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','获取课程信息失败')));
        }

        $info['logoUrl'] = !empty($info['co_logo']) ? TFS_APIURL.'/'.$info['co_logo'] : _TEMP_PUBLIC_."/images/none.png";
        $data['jsData']  = json_encode($info);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
