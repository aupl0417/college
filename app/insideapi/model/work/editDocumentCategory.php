<?php

class editDocumentCategory extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        $sql = "select * from t_document_category where dc_id = '".$this->options['id']."' limit 1";

        $data = $this->db->getRow($sql);


        $temp = array(
            'dc_id' => $data['dc_id'],
            'dc_order' => $data['dc_order'],
        );


        $this->setReplaceData($temp);
        $this->setTempAndData();
        $this->show();

    }

}
