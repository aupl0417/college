<?php

class editDocumentCategory_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {


        $p = array(
            'dc_order' => $this->options['dc_order'],
        );

        if($this->db->update('t_document_category',$p, "dc_id = '".$this->options['dc_id']."'")){
            $this->show(message::getJsonMsgStruct('1001','操作成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct('1002','操作失败'));
        }

    }

}
