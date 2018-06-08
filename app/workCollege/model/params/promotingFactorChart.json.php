<?php

class promotingFactorChart_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [40701]);
    }

    function run() {
		$db = new MySql();
		
		$type = (isset($this->options['type']) && F::isNotNull($this->options['type'])) ? $this->options['type'] : 'factor_given';//默认取出赠送积分系数		

		$sql = "SELECT pf_val as val, DATE_FORMAT(pf_time, '%Y-%m-%d') AS `date`, pf_time AS `time` FROM `t_promoting_factor` WHERE pf_type='".$type."' GROUP BY `date` ORDER BY `date` ASC;";
		//, 'round' AS `bullet`
		$result = $db->getAll($sql);
		if(!$result){
			$this->show(message::getJsonMsgStruct('1002', 'none'));
			exit;
		}
		
		$now = F::mytime();
		$today = F::mytime('Y-m-d');
		$end = end($result);
		$endDate = $end['date'];
		if(strtotime($endDate) < strtotime($today)){
			$result[] = [
				'date' 			=> $today,
				'time' 			=> $now,
				'val'			=> $end['val'],
				'customBullet'	=> "/app/public/assets/images/redstar.png"
			];
		}
		$this->show(message::getJsonMsgStruct('1001', $result));
	}
}