<?php
/*=============================================================================
#     FileName: editSite.json.php
#         Desc: 修改课室
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-29 14:37:04
#      History:
=============================================================================*/

class editSite_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [500401]);
    }

    function run() {
        $options = $this->options;
        $db = new MySql();

        $needParamer = array(
            'name'         => '课室名',
            'area'         => '所在区域',
            'address'      => '详细地址',
            //'tantgCollege' => '所属分院',
            'property'     => '产权类型',
            'type'         => '课室类型',
            'id'           => '课室ID'
        );

        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($db->count('tang_trainingsite',"tra_name='".$options['name']."' AND tra_id<>'{$options['id']}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课程，无需重复添加')));
        }

        $data = array(
            'tra_name'     => trim($options['name']),
            'tra_areaId'   => trim($options['area']),
            'tra_address'  => $options['address'],
            'tra_property' => $options['property'],
            'tra_type'     => $options['type'],
            'tangCollege'  => 2,
        );

        if(1 != $db->update('tang_trainingsite',$data," tra_id='{$options['id']}'")){
            die($this->show(message::getJsonMsgStruct(1002,'修改失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'修改成功'));
    }
}
