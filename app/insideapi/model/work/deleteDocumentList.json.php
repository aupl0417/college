<?php

class deleteDocumentList_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        $sql = "DELETE FROM t_document_list WHERE `dl_id`= '".$this->options['id']."' limit 1";
        if($this->db->exec($sql)){
            $this->show(message::getJsonMsgStruct(1001,'删除成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'删除失败'));
        }

    }

}
