<?php

class checkApplication_json extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {

        $db = new MySql();
        $p = array(
            'da_status' => $this->options['da_status'],
            'da_mome' => $this->options['da_mome'],
        );

        if($db->update('t_develop_application',$p,"da_id='".$this->options['da_id']."'")){
            $this->show(message::getJsonMsgStruct('1001','操作成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct('1002','操作失败'));
        }

    }

}
