<?php

class editFaq extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {
        $db = new MySql();

        $sql = "select * from t_question_category order by qc_order desc";
        $data = $db->getAll($sql);
        $this->setLoopData('categoryData',$data);

        $sql = "select * from t_question_answer where qa_id = '".$this->options['id']."' limit 1";

        $data = $db->getRow($sql);


        $temp = array(
            'qa_id' => $data['qa_id'],
            'qa_qc_id' => $data['qa_qc_id'],
            'qa_question' => $data['qa_question'],
            'qa_answer' => $data['qa_answer'],
            'qa_creatime' => $data['qa_creatime'],
        );


        $this->setReplaceData($temp);
		$this->setTempAndData();
		$this->show();
    }

}