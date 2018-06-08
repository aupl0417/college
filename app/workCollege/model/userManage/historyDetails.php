<?php
/**
 * 用户操作日志
 * Auther:JoJoJing
 * Time： 2016-07-29
 * @param type 【0 用户操作日志，1 雇员操作日志】
 */

class historyDetails extends worker {
	function __construct($options) {
		$type = !isset($options['type']) ? 0 : $options['type'];
		$power = array(
			'0' => 6010701,  //用户操作日志权限
			'1' => 6010702,  //账户操作权限
		);
		parent::__construct($options, [$power[$type]]);
	}

	function run() {
		$mgdb = new mgdb();
		$mongoLogs = new mongoLogs();

		$logList = $mgdb->where(array('_id' => new MongoId("{$this->options['_id']}")))->get('logs');

		$logDetail = $mongoLogs->logTemplate($logList[0]['log_type_id'],$logList[0]);

		$data = array(
			'code'          => 60107,
			'logDetail'		=> $logDetail,
		);

		$this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
	}
}
