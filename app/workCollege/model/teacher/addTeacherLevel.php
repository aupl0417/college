<?php

class addTeacherLevel extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50030103,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$courseLevel = array(1 => '初级', 2 => '中级课及以下', 3 => '高级课及以下', 4 => '其它课', 5 => '所有等级');
		$string = '';
		foreach($courseLevel as $key=>$val){
	        $string .= "<option value='".$key."'>" . $val . "</option>";
		} 
		$data['courseLevel'] = $string;
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
    
}
