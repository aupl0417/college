<?php

class addDocument_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;
        $p = array(
            'dl_dc_id' => $options['dl_dc_id'],
            'dl_title' => $options['dl_title'],
            'dl_content' => $options['content'],
            'dl_creatime' => F::mytime(),
            'dl_updatime' => '0000-00-00 00:00:00',
            'dl_version' => $options['dl_version'],
            'dl_author' => $_SESSION['userNick'],
        );

        if($this->db->insert('t_document_list',$p)){
            $this->show(message::getJsonMsgStruct(1001,'添加成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'添加失败'));
        }

    }

}
