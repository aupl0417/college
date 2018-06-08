<?php

class editDocument_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {


        $p = array(
            'dl_title' => $this->options['dl_title'],
            'dl_version' => $this->options['dl_version'],
            'dl_content' => $this->options['content'],
            'dl_author' => $_SESSION['userNick'],
            'dl_updatime' => F::mytime(),
        );

        if($this->db->update('t_document_list',$p, "dl_id = '".$this->options['id']."'")){
            $this->show(message::getJsonMsgStruct('1001','操作成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct('1002','操作失败'));
        }

    }

}
