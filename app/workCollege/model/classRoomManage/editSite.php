<?php
/*=============================================================================
#     FileName: editSite.php
#         Desc: 修改课室
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-29 11:02:08
#      History:
#      Paramer:
=============================================================================*/
class editSite extends worker {
    function __construct($options) {
        parent::__construct($options, [500401]);
    }

    function run() {
        $options = $this->options;

        if (empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct('1002','获取课室ID错误')));
        }

        $db   = new MySql();
        $info = $db->getRow("SELECT * FROM tang_trainingsite WHERE tra_id='{$options['id']}'");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','获取课室信息失败')));
        }

        $data = array(
            'code'         => '500401',
            'tempId'       => 'temp_'.F::getGID(),
            'jsData'       => json_encode(array(
                'property' => F::getAttrs(5),
                'typeList' => F::getAttrs(6),
                'info'     => $info
            ))
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
