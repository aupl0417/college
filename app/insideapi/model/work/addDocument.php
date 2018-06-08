<?php

class addDocument extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        $sql = "select * from t_document_category";
        $data = $this->db->getAll($sql);

        $this->setLoopData('t_document_category',$data);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
