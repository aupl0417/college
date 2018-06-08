<?php

class addPromotingFactor_json extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [40702]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		$time = isset($options['time']) ? $options['time'] : '';
		if($time == ''){
			$this->show(message::getJsonMsgStruct('1002', '请选择生效时间'));
			exit;
		}
		$time = F::verifyDateTime($time);
		
		if(!$time){
			$this->show(message::getJsonMsgStruct('1002', '无效时间'));
			exit;
		}
		if($time->getTimestamp() < time()){
			//$this->show(message::getJsonMsgStruct('1002', '生效时间时间不能晚于当前时间'));
			//exit;
		}
		
		$id = sprintf('%s%03d', $time->format('YmdHis'), rand(0, 999));
		
		$time = $time->format('Y-m-d H:i:s');
	
		$val = isset($options['val']) ? F::fmtNum($options['val']) : 1;
		
		$type = isset($options['type']) ? $options['type'] : '';
		if($type == ''){
			$this->show(message::getJsonMsgStruct('1002', '系数类型错误'));
			exit;
		}
		
		$typeName = isset($options['typeName']) ? $options['typeName'] : '';
		if($typeName == ''){
			$this->show(message::getJsonMsgStruct('1002', '系数类型错误'));
			exit;
		}
		
		if(!attrib::clearPromotingFactor($type)){
			$this->show(message::getJsonMsgStruct('1002', '清空缓存失败'));
		};
		
		$insert = [
			'pf_id'		=> $id,
			'pf_time'	=> $time,
			'pf_memo'	=> $typeName,
			'pf_type'	=> $type,
			'pf_val'	=> $val
		];
		if($this->db->insert('t_promoting_factor', $insert) == 1){
			//记日志
			$insert['memo'] = '添加推广系数';
			log::writeLogMongo(40702,'t_promoting_factor',$id,$insert);
			$this->show(message::getJsonMsgStruct('1001'));
		}else{
			$this->show(message::getJsonMsgStruct('1002', '添加失败'));
		}
    }
	
}
