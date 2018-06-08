<?php
/*=============================================================================
#     FileName: schStudentInfo.php
#         Desc: 查询报名信息
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:44:52
#      History:
=============================================================================*/
class schStudentInfo extends worker{
    function __construct($options) {
        parent::__construct($options, [500105]);
    }
    public function run(){
        $options  = $this->options;

        $data = array();

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
