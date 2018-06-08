<?php

class detailInteraction extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040502]);
    }
	
    function run() {
        
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$data = array(
		    'code'          => 50040502,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		$id   = $this->options['id'] + 0;
        $sql = "select tsi_id as id,co_name as courseName,username,tsi_createTime as createTime,tsi_title as title, tsi_isPublic,tsi_content as content from tang_teacher_student_interaction 
               LEFT JOIN tang_course on tsi_courseId=co_id 
               LEFT JOIN tang_ucenter_member on tsi_userId=id 
               where tsi_id='" . $id . "'";
        
        $db  = new MySql();
        $result = $db->getRow($sql);
        
        $replySql = 'select tsi_id as id,tsi_content,tsi_createTime,tsi_userId,tsi_teacherId,tsi_status as state from tang_teacher_student_interaction 
                    where tsi_pid="'.$result['id'].'"';
        
        $replyData = $db->getAll($replySql);
        
        foreach($replyData as $key=>&$val){
            if($val['tsi_userId'] == 0){
                $fieldName = 'tsi_teacherId';
            }else if($val['tsi_teacherId'] == 0) {
                $fieldName = 'tsi_userId';
            }
            $val['username'] = $db->getField('select username from tang_ucenter_member where id="' . $val[$fieldName] . '"');
            $val['stateMsg'] = $val['state'] == 1 ? '显示' : '隐藏';
        }
        
        $result['replyCount'] = count($replyData);
        $result['isShare'] = $result['tsi_isPublic'] == 1 ? 'checked="checked"' : '';
        $result['unShare'] = $result['tsi_isPublic'] == 0 ? 'checked="checked"' : '';
        
        $data = array_merge($data, $result);
        
        $this->setLoopData('replyData', $replyData);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
