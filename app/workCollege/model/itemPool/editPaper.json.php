<?php

class editPaper_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040302]);			
    }
    //暂时不做
    function run() {
        
        if(empty($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002',  '参数错误'));exit;
        }
        
        if(empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写题目问题'));exit;
        }
        
        if(empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        if(empty($this->options['question'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填选择题目'));exit;
        }
        
        if(empty($this->options['score'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写问题分数'));exit;
        }
        
        //组合数据--题目id对应相应的分数
        $question = $this->options['question'];
        $score = $this->options['score'];
        $count = count($question);
        $questionInfo = array();
        for($i=0; $i < $count; $i++){
            $questionInfo[$i]['id'] = $question[$i];
            $questionInfo[$i]['score'] = $score[$i];
        }
        
        $id = $this->options['id'] + 0;
        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $data = array(
            'cep_name'        => $this->options['name'],
            'cep_courseId'    => $this->options['courseId'] + 0,
            'cep_questionIds' => serialize($questionInfo),
            'cep_totalScore'  => $this->options['totalScore'],
            'cep_isPublic'    => $this->options['isPublic'] + 0,
            'cep_updateTime'  => date('Y-m-d H:i:s', $time),
        );
        
        $db = new MySql();
        $res = $db->update('tang_course_exam_paper', $data, 'cep_id="' . $id . '"');
        $res === false && exit($this->show(message::getJsonMsgStruct('1002', '编辑失败')));
        
        $this->show(message::getJsonMsgStruct('1001', '编辑成功'));exit;
    }
}
