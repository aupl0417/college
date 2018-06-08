<?php

class deleteDocumentCategory_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        $sql = "select * from t_document_list where dl_dc_id = '".$this->options['id']."'";
        $data = $this->db->getRow($sql);
        if($data){
            $this->show(message::getJsonMsgStruct(1002,'删除失败,分类下有文档'));
            exit;
        }

        $sql = "DELETE FROM t_document_category WHERE `dc_id`= '".$this->options['id']."' limit 1";
        if($this->db->exec($sql)){
            $this->show(message::getJsonMsgStruct(1001,'删除成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'删除失败'));
        }

    }

}
