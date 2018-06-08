<?php

class getExamList_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, []);			
    }
    //暂时不做
    function run() {
        
        if(empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        $db = new MySql();
        $courseId = $this->options['courseId'] + 0;
        $examItems = $db->getAll('select cre_id as id,cre_name as name from tang_course_exam where cre_courseId="' . $courseId . '"');
        dump($examItems);die;
        if(isset($this->options['cid'])){
            $id = $this->options['cid'] + 0;
            $sql = "select cep_questionIds from tang_course_exam_paper where cep_id='" . $id . "'";
            $question = $db->getField($sql);
            $questionInfo = unserialize($question);
            $itemKey = array_column($examItems, 'id');
            foreach ($questionInfo as $key=>&$val){
                $val['listString'] = $this->getSelectString($examItems, $val['id']);
                $val['keys'] = $itemKey;
                if(!in_array($val['id'], $itemKey)){
                    //$val['score'] = '';
                    unset($questionInfo[$key]);
                }
            }
            $this->show(message::getJsonMsgStruct('1001', $questionInfo));exit;
        }else {
            $listString = $this->getSelectString($examItems);
            $this->show(message::getJsonMsgStruct('1001', $listString));exit;
        }
        
    }
    
    private function getSelectString($data, $itemId = ''){
        if(!is_array($data) || empty($data)){
            return;
        }
    
        $string = '';
        foreach($data as $key=>$val){
            if($val['id'] == $itemId && !empty($itemId)){
                $string .= '<option value="' . $val['id'] . '" selected="selected">' . $val['name'] . '</option>';
            }else {
                $string .= '<option value="' . $val['id'] . '">' . $val['name'] . '</option>';
            }
        }
        
        return $string;
    }
}
