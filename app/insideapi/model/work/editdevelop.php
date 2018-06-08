<?php

class editdevelop extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        $sql = "select * from t_develop_partner where dp_id = '".$this->options['id']."'";
        $data = $this->db->getRow($sql);

        $temp = array(
            'dp_uid' => $this->db->getField("select u_nick from t_user where u_id = '".$data['dp_uid']."'"),
            'dp_contact' => $data['dp_contact'],
            'dp_email' => $data['dp_email'],
            'dp_tel' => $data['dp_tel'],
            'dp_mome' => $data['dp_mome'],
            'dp_status' => $data['dp_status'],
            'dp_id' => $data['dp_id'],
        );
        $this->setReplaceData($temp);
        $this->setTempAndData();
        $this->show();
    }

}
