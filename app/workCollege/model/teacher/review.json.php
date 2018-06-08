<?php

class review_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030104]);
    }
	
    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
		F::isEmpty($this->options['state']) && die($this->show(message::getJsonMsgStruct('1002', '请选择审核状态')));
		F::isEmpty($this->options['reason']) && die($this->show(message::getJsonMsgStruct('1002', '请填写理由')));
		
		$id = $this->options['id'] + 0;
		$data = array(
		    'tp_status' => $this->options['state'] + 0,
		    'tp_reason' => $this->options['reason']
		);
		$db = new MySql();
		$res = $db->update('tang_teacher_promotion', $data, 'tp_id="' . $id . '"');
		$res === false && die($this->show(message::getJsonMsgStruct('1002', '操作失败')));
		die($this->show(message::getJsonMsgStruct('1001', '操作成功'))); 
    }
}
