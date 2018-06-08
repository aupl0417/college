<?php

class addDocumentCategory_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;
        $p = array(
            'dc_name' => $options['dc_name'],
            'dc_order' => $options['dc_order'],
        );

        if($this->db->insert('t_document_category',$p)){
            $this->show(message::getJsonMsgStruct(1001,'添加成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'添加失败'));
        }

    }

}
