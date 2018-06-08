<?php

class editdevelop_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {


        $p = array(
            'dp_contact' => $this->options['dp_contact'],
            'dp_tel' => $this->options['dp_tel'],
            'dp_email' => $this->options['dp_email'],
            'dp_status' => $this->options['dp_status'],
            'dp_mome' => $this->options['dp_mome'],
        );

        if($this->db->update('t_develop_partner',$p, "dp_id = '".$this->options['dp_id']."'")){
            $this->show(message::getJsonMsgStruct('1001','操作成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct('1002','操作失败'));
        }

    }

}
