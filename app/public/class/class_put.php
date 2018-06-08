<?php

/**
 * 提成嘉奖/升级奖励/代理奖励加入队列的操作
 * adadsa
 * 2016-07-24
 */
class put {
	/* 提成嘉奖加入队列 */
	static function award($orderid){
		if(!$orderid){
			return false;
		}
		return queues::producter('erp', 'taskAward', [$orderid], 1024, 0);
	}
	
	/* 升级奖励加入队列 */
	static function upgrade($orderid){
		if(!$orderid){
			return false;
		}
		return queues::producter('erp', 'taskUpgrade', [$orderid], 2048, 7*86400);
	}
}

?>