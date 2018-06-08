<?php
/*=============================================================================
#     FileName: addSite.json.php
#         Desc: 添加课室
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-29 17:34:22
#      History:
=============================================================================*/
class addSite_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [500401]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array(
            'name'         => '课室名',
            'area'         => '所在区域',
            'address'      => '详细地址',
            //'tantgCollege' => '所属分院',
            'property'     => '产权类型',
            'type'         => '课室类型',
        );

        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_trainingsite',"tra_name='".$options['name']."'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课室，无需重复添加')));
        }

        $data = array(
            'tra_createTime' => F::mytime(),
            'tra_name'       => trim($options['name']),
            'tra_areaId'     => trim($options['area']),
            'tra_address'    => $options['address'],
            'tra_property'   => $options['property'],
            'tra_type'       => $options['type'],
            'tangCollege'    => 2,
        );

        if(1 != $this->db->insert('tang_trainingsite',$data)){
            die($this->show(message::getJsonMsgStruct(1002,'添加失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'添加成功'));
    }
}
