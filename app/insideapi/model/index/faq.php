<?php

class faq extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

        $db = new MySql();
        $options = $this->options;

        //技术分类
        $sql = "select * from t_question_category order by qc_order asc";
        $category = $db->getAll($sql);
        foreach($category as $key=>$value){
            $category[$key]['count'] = $db->count('t_question_answer','qa_qc_id = '.$value['qc_id']);
        }

        //分类信息详情
        if(isset($options['qc_id'])){
            //详情内容
            $sql = "select qa_id,qa_question,qa_answer from t_question_answer where qa_qc_id = '".$options['qc_id']."'";
            $data = $db->getAll($sql);
            foreach($data as $key=>$value){
                $data[$key]['qa_answer'] = html_entity_decode($value['qa_answer']);
            }
            //分类名称
            $sql = "select qc_name from t_question_category where qc_id = '".$options['qc_id']."'";
            $c_name = $db->getRow($sql);
        }else{
            //一进入页面显示qc_order最小的那一条数据
            $sql = "select qc_id,qc_name from t_question_category order by qc_order asc limit 0,1";
            $qc = $db->getRow($sql);
            $c_name['qc_name'] = $qc['qc_name'];
            //详情内容
            $sql = "select qa_id,qa_question,qa_answer from t_question_answer where qa_qc_id = '".$qc['qc_id']."'";
            $data = $db->getAll($sql);
            foreach($data as $key=>$value){
                $data[$key]['qa_answer'] = html_entity_decode($value['qa_answer']);
            }
        }
        $this->setLoopData('data',$data);
        $this->setLoopData('category',$category);
        $this->setReplaceData($c_name);
        $this->setTempAndData('faq/faq');
        $this->show();
    }

}