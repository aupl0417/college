<?php

class unread_json extends guest {

    function run() {
		$this->show(message::getJsonMsgStruct('1001', []));
		die;
		$db = new MySql();
		$sql = "SELECT * FROM t_mailbox_readed
				LEFT JOIN t_mailbox ON mbr_mbID = mb_id
				WHERE mbr_uid = '" . $_SESSION['userID'] . "' and mbr_isReaded = 0 and mbr_isDelete = 0 order by mb_id desc";
		$rs = $db->getAll($sql);
		foreach($rs as $k=>$v){
			$rs[$k]['mb_ctime'] = $this->format_date($v['mb_ctime']);
			$rs[$k]['url'] = 'https://'.WORKERURL;
		}
		$this->show(message::getJsonMsgStruct('1001', $rs));
    }

	//格式化时间
	private function format_date($time) {
		$nowtime = time();
		$difference = $nowtime - strtotime($time);

		switch ($difference) {

			case $difference <= '60' :
				$msg = '刚刚';
				break;

			case $difference > '60' && $difference <= '3600' :
				$msg = floor($difference / 60) . '分钟前';
				break;

			case $difference > '3600' && $difference <= '86400' :
				$msg = floor($difference / 3600) . '小时前';
				break;

			case $difference > '86400' && $difference <= '2592000' :
				$msg = floor($difference / 86400) . '天前';
				break;

			case $difference > '2592000' &&  $difference <= '7776000':
				$msg = floor($difference / 2592000) . '个月前';
				break;
			case $difference > '7776000':
				$msg = '很久以前';
				break;
		}

		return $msg;
	}

}
