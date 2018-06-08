<?php
/*=============================================================================
#     FileName: editTeam.php
#         Desc: 分组设置
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-12 09:43:27
#      History:
#      Paramer:
=============================================================================*/
class editTeam extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options    = $this->options;
        $db         = new MySql();
        $data['id'] = $options['id']+0;

        $sql = "SELECT cl_teamNum,cl_teamStudentNum,cl_name,cl_allowableNumber FROM tang_class WHERE cl_id='{$data['id']}'";
        $info = $db->getRow($sql);

        if (!empty($info)) {
            $data = array_merge($data,$info);
        }

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
