<?php

class addFaq_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;
        $p = array(
            'qa_qc_id' => $options['qa_qc_id'],
            'qa_question' => $options['qa_question'],
            'qa_answer' => $options['content'],
            'qa_creatime' => F::mytime(),
        );

        if($this->db->insert('t_question_answer',$p)){
            $this->show(message::getJsonMsgStruct(1001,'添加成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'添加失败'));
        }

    }

}
