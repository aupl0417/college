<?php

class delete_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [500501]);
    }

    function run() {	
		$options = $this->options;
// 		dump($options);die;

        $id = isset($options['id'])?$options['id']: "";
		if($id == "" ){//
			$this->show(message::getJsonMsgStruct('1002', 'id错误'));//ID错误
			exit;
		}
		$where = "e_id = '".$id."'";
		$db = new MySql();
		$result = $db->delete('t_employee', $where);
		//记录操作日志
		$data['memo'] = '雇员删除';//尽量写得详细一点点了
		log::writeLogMongo(202, 't_employee', $id, $data);
		
		if($result){
            $this->show(message::getJsonMsgStruct('1001', "删除成功"));//成功	
		}else{
		    $this->show(message::getJsonMsgStruct('1002', "删除失败"));//失败
		}
	}
}
