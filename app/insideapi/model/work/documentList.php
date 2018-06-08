<?php

class documentList  extends guest {

    function __construct($options) {        		
        parent::__construct($options, [60110]);			
    }
    function run() {

        $db = new MySql();
        $sql = "select * from t_document_category";
        $data = $db->getAll($sql);
//dump($data);
        $this->setLoopData('t_document_category',$data);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
