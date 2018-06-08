<?php
/*=============================================================================
#     FileName: editCourseDirection.php
#         Desc: 修改课程
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 21:26:32
#      History:
#      Paramer:
=============================================================================*/
class editCourseDirection extends worker {
    function __construct($options) {
        parent::__construct($options, [50010403]);
    }

    function run() {
        $options = $this->options;
        $db      = new MySql();

        $data = array(
            'code' => '50010403',
        );

        $info = $db->getRow("SELECT * FROM tang_study_direction WHERE sd_id='{$options['id']}'");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','获取课程分类失败')));
        }

        $data = array_merge($data,$info);
        $data['stateList'] = F::array2Options(array(-1=>'失效',0=>'有效'),array($info['sd_state']));

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
