<?php

class editFaq_json extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {
        $db = new MySql();
        $p = array(
            'qa_qc_id' => $this->options['qa_qc_id'],
            'qa_question' => $this->options['qa_question'],
            'qa_answer' => $this->options['content'],
        );

        if($db->update('t_question_answer',$p, "qa_id = '".$this->options['qa_id']."'")){
            $this->show(message::getJsonMsgStruct('1001','操作成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct('1002','操作失败'));
        }
    }

}