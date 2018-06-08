<?php

class importStudent extends worker {
    
    function __construct($options) {        		
        parent::__construct($options, [50020101]);		 	
    }
    
    function run() {
		
		if(isset($this->options['username']) && !empty($this->options['username'])){
		    $param = $this->options['username'];
		}else if(isset($this->options['mobile']) && !empty($this->options['mobile'])){
		    $param = $this->options['mobile'];
		}else {
		    return false;//暂时
		}
		
        $params['input'] = $param;
        $path = '/user/getUser';
        $sdk  = new openSdk();
        $data = $sdk->request($params, $path);
		
        !is_array($data) && die($this->show(message::getJsonMsgStruct('1002', $data)));
        
		$db = new MySql();
		$sql = 'select cl_id as classId,cl_name as className from tang_class where cl_status=1 and cl_state<>-1';
		$result = $db->getAll($sql);
		
		$this->setLoopData('classInfo', $result);
		$this->setReplaceData($data['info']);
		$this->setTempAndData();
		$this->show();
    }
    
}
