<?php

class detail extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040501]);
    }
	
    function run() {
        
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$data = array(
		    'code'          => 50040501,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		$id   = $this->options['id'] + 0;
        $sql = "select tc_id,cl_name as className,cl_id,username,tc_createTime,tc_isPublic,tc_content from tang_teacher_comment 
               LEFT JOIN tang_class_table on tc_classTableId=cta_id 
               LEFT JOIN tang_class on cta_classId=cl_id 
               LEFT JOIN tang_ucenter_member on tc_userId=id 
               where tc_id='" . $id . "'";
        
        $db  = new MySql();
        $result = $db->getRow($sql);
        
        $classList = $db->getAll('select cl_id, cl_name from tang_class');//暂时全部选择，以后根据所在分院及下属分院来获取
        $data['classList'] = '';
        foreach ($classList as $key=>$val) {
            if($val['cl_id'] == $result['cl_id']){
                $data['classList'] .= "<option value='" . $val['co_id'] . "' selected='selected'>" . $val['co_name'] . "</option>";
            }else {
                $data['classList'] .= "<option value='" . $val['co_id'] . "'>" . $val['co_name'] . "</option>";
            }
        }
        
        $result['isShare'] = $result['tc_isPublic'] == 1 ? 'checked="checked"' : '';
        $result['unShare'] = $result['tc_isPublic'] == 0 ? 'checked="checked"' : '';
        
        $data = array_merge($data, $result);
        
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
