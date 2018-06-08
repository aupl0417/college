<?php

class addPaper_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040302]);			
    }
    
    function run() {
//         dump($this->options);die;
        if(empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写题目问题'));exit;
        }
        
        if(empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        if(empty($this->options['question'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写题目描述'));exit;
        }
        
        if(empty($this->options['score'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写选项A'));exit;
        }
        
        if(empty($this->options['totalScore'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写选项B'));exit;
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
        
        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $data = array(
            'cep_name'        => $this->options['name'],
            'cep_courseId'    => $this->options['courseId'] + 0,
            'cep_userId'      => $_SESSION['userID'],
            'cep_questionIds' => serialize($questionInfo),
            'cep_totalScore'  => $this->options['totalScore'],
            'cep_isPublic'    => $this->options['isPublic'] + 0,
            'cep_updateTime'  => date('Y-m-d H:i:s', $time),
            'cep_createTime'  => date('Y-m-d H:i:s', $time),
        );
        
        $db = new MySql();
        $id = $db->insert('tang_course_exam_paper', $data);
        !$id && exit($this->show(message::getJsonMsgStruct('1002', '添加失败')));
        
        $this->show(message::getJsonMsgStruct('1001', '添加成功'));exit;
            
    }
}
