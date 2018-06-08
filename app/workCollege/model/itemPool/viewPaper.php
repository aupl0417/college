<?php

class viewPaper extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040302]);
    }
	
    function run() {
        
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$id   = $this->options['id'] + 0;
		
        $sql = "select cep_id,cep_name,cep_courseId,cep_isPublic,cep_totalScore,cep_questionIds,co_id from tang_course_exam_paper 
               LEFT JOIN tang_course on cep_courseId=co_id 
               where cep_id='" . $id . "'";
        
        $db  = new MySql();
        $data = $db->getRow($sql);
        
        $courseList = $db->getAll('select co_id, co_name from tang_course');//暂时全部选择，以后根据所在分院及下属分院来获取
        $data['courseList'] = '';
        foreach ($courseList as $key=>$val) {
            if($val['co_id'] == $data['co_id']){
                $data['courseList'] .= "<option value='" . $val['co_id'] . "' selected='selected'>" . $val['co_name'] . "</option>";
            }else {
                $data['courseList'] .= "<option value='" . $val['co_id'] . "'>" . $val['co_name'] . "</option>";
            }
        }
        
        //获取相应条件下的题目列表
        $examItems = $db->getAll('select cre_id as id,cre_name as name from tang_course_exam');
        $question = unserialize($data['cep_questionIds']);
        $length = count($question);
        foreach($question as $key=>&$val){
            $val['listString'] = $this->getSelectString($examItems, $val['id']);
            $val['mark'] = '-';
            $val['class'] = 'delete';
            if($key == 0){
                $val['mark']  = '+';
                $val['class'] = 'add';
            }
        }
        
        $data['isShare'] = $data['cep_isPublic'] == 1 ? 'checked="checked"' : '';
        $data['unShare'] = $data['cep_isPublic'] == 0 ? 'checked="checked"' : '';
        
        $this->setLoopData('questions', $question);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
    
    //
    private function getSelectString($data, $itemId){
        if(!is_array($data) || empty($data)){
            return;
        }
        
        $string = '';
        foreach($data as $key=>$val){
            if($val['id'] == $itemId){
                $string .= '<option value="' . $val['id'] . '" selected="selected">' . $val['name'] . '</option>';
            }else {
                $string .= '<option value="' . $val['id'] . '">' . $val['name'] . '</option>';
            }
        }
        
        return $string;
    }
    
    
}
