<?php

class view extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040301]);
    }
	
    function run() {
        if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$id   = $this->options['id'] + 0;
		
        $sql = "select cre_id,cre_name,cre_courseId,cre_description,cre_a,cre_b,cre_c,cre_d,cre_answer,cre_isPublic,co_id from tang_course_exam 
               LEFT JOIN tang_course on cre_courseId=co_id 
               where cre_id='" . $id . "'";
        
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
        
        $data['isShare'] = $data['cre_isPublic'] == 1 ? 'checked="checked"' : '';
        $data['unShare'] = $data['cre_isPublic'] == 0 ? 'checked="checked"' : '';
        
        
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
