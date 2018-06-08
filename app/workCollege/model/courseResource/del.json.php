<?php

class del_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);			
    }
    
    function run() {
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$cid = $this->options['id'] + 0;
		$db = new MySql();
		try {
		    $db->beginTRAN();
		    $result = $db->delete('tang_course_resource', 'cr_id="' . $cid . '"');
		    if(!$result){
		        throw new Exception('删除主表数据失败');
		    }
		    
		    $count = $db->getField("select count(crd_id) as count from tang_course_resource_file where crd_resourceId='" . $cid . "'");
		    if($count){
		        $res = $db->delete('tang_course_resource_file', "crd_resourceId='" . $cid . "'");
		        if(!$res){
		            throw new Exception('删除附表数据失败');
		        }
		    }
		    
		    $db->commitTRAN();
		    
		    $this->show(message::getJsonMsgStruct('1001', '删除成功'));exit;
		    
		} catch (Exception $e) {
		    $db->rollBackTRAN();
		    $this->show(message::getJsonMsgStruct('1002',  '删除失败'));exit;
		}
		
    }
}
