<?php

class editDocument extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {

        $db = new MySql();

        $sql = "select * from t_document_category";
        $data = $db->getAll($sql);
        $this->setLoopData('categoryData',$data);

        $sql = "select * from t_document_list where dl_id = '".$this->options['id']."' limit 1";

        $data = $db->getRow($sql);


        $temp = array(
            'dl_dc_id' => $data['dl_dc_id'],
            'dl_title' => $data['dl_title'],
            'dl_author' => $data['dl_author'],
            'dl_version' => $data['dl_version'],
            'dl_content' => $data['dl_content'],
            'dl_id' => $data['dl_id'],
        );


        $this->setReplaceData($temp);
		$this->setTempAndData();
		$this->show();
    }

}