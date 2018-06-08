<?php

class add extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);
    }
	
    function run() {
		$type = $this->options['type'];
		if(F::isEmpty($type)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$data = array(
		    'code'          => 40201,
			'tempId'		=> 'temp_'.F::getGID(),
		    'type'          => intval($type)
		);
		
        $sql = "select co_id, co_name from tang_course where co_state=1";//暂时全部选择，以后根据所在分院及下属分院来获取
        $db  = new MySql();
        $courseList = $db->getAll($sql);
        switch(intval($type)){
            case 0:
                $template = 'addFile';
                break;
            case 1:
                $template = 'addVideo';
                break;
            case 2:
                $data['resourceMenu'] = '网页文章';
                $data['resourceButton'] = '<input name="url[]" type="text" class="form-control col-md-8" style="width:50%;" placeholder="请输入网页文章地址"><button type="button" class="btn btn-success button"><i class="fa"></i> 添加地址</button>';
                $data['addButton'] = '<input name="url[]" type="text" class="form-control col-md-8" style="width:50%;" placeholder="请输入网页文章地址"><button type="button" class="btn btn-success" onclick="delButton(this);"><i class="fa"></i> 删 除</button>';
                break;
        }
        
        $this->setLoopData('courseList', $courseList);
		$this->setReplaceData($data);
        $this->setTempAndData($template);
        $this->show();
    }
}
