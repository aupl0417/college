<?php
 
class add_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040301]);			
    }
    
    function run() {
        if(empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写题目问题'));exit;
        }
        
        if(empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        if(empty($this->options['description'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写题目描述'));exit;
        }
        
        if(empty($this->options['cre_a'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写选项A'));exit;
        }
        
        if(empty($this->options['cre_b'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写选项B'));exit;
        }
        
        if(empty($this->options['cre_c'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写选项C'));exit;
        }
        
        if(empty($this->options['cre_d'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写选项D'));exit;
        }
        
        if(empty($this->options['answer'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写答案'));exit;
        }
        
        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $data = array(
            'cre_name'        => $this->options['name'],
            'cre_courseId'    => $this->options['courseId'] + 0,
            'cre_userId'      => $_SESSION['userID'],
            'cre_description' => $this->options['description'],
            'cre_a'           => $this->options['cre_a'],
            'cre_b'           => $this->options['cre_b'],
            'cre_c'           => $this->options['cre_c'],
            'cre_d'           => $this->options['cre_d'],
            'cre_answer'      => $this->options['answer'],
            'cre_isPublic'    => $this->options['isPublic'] + 0,
            'cre_updateTime'  => date('Y-m-d H:i:s', $time),
            'cre_createTime'  => date('Y-m-d H:i:s', $time),
        );
        
        $db = new MySql();
        $id = $db->insert('tang_course_exam', $data);
        if(!$id){
            $this->show(message::getJsonMsgStruct('1002', '添加失败'));exit;
        }
        
        $this->show(message::getJsonMsgStruct('1001', '添加成功'));exit;
            
    }
}
