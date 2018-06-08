<?php

class addFaq extends guest {
    function __construct($options) {
        parent::__construct($options, [8]);
    }

    function run() {
        $db = new MySql();
        $sql = "select * from t_question_category order by qc_order desc";
        $data = $db->getAll($sql);
        $this->setLoopData('categoryData',$data);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
