<?php
/**
 * 用户操作日志
 * Auther:JoJoJing
 * Time： 2016-07-29
 * @param type 【0 用户操作日志，1 雇员操作日志】
 */

class history extends worker {
	function __construct($options) {
		$type = !isset($options['type']) ? 0 : $options['type'];
		$power = array(
			'0' => 6010701,  //用户操作日志权限
			'1' => 6010702,  //账户操作权限
		);
		parent::__construct($options, [$power[$type]]);
	}

	function run() {
		$type = isset($this->options['type']) ? $this->options['type'] : 0; //选中的tab
		$id = isset($this->options['id']) ? $this->options['id'] : '';  //当前会员ID（32）
		if(empty($id) && strlen($id) != 32){
			$this->show(message::getFormatMsg('1002','参数错误'));
			exit;
		}

		$data = array(
			'code'          => 60107,
			'type'    		=> $type,
			'id'			=> $id,
		);

		$this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
	}
}
