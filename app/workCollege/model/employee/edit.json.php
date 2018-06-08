<?php

class edit_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [500501]);
    }

    function run() {	
		$options = $this->options;
//         dump($options);die;
        $id = isset($options['id'])?$options['id']: "";
		if($id == "" ){//
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//ID错误	
			exit;
		}
		$db = new MySql();
		$result = $db->getRow("SELECT * FROM
				`t_employee` as te
				LEFT JOIN
				(
				SELECT dm_id,dm_name,dm_code FROM
				`t_organization`
				) as u ON u.dm_id = te.e_departmentID left join t_duty on t_duty.dt_id=te.e_dutyID where te.e_id='".$id."'");
		$data = $db->getAll("select dt_id, dt_name from t_duty");
		$department = $db->getAll("select dm_id, dm_name from t_organization where dm_isEnd= 1");
// 		dump($result);die;

		$data = array_column($data,'dt_name','dt_id');
		$info = F::array2Options($data,[$result['dt_id']]);
// 		dump($result);die;
		$department = array_column($department,'dm_name','dm_id');
		$in = F::array2Options($department,[$result['dm_id']]);
		$date = array(
			'id'		   => $result['e_id'], 
			'name'		   => $result['e_name'], 
		    'charname'	   => $result['e_charName'],
		    'phone'        => $result['e_tel'],
			'organ'        => $result['dm_code'], 
		    'organization' => $in,
			'state'	       => $result['e_state'], 
		    'duty'  => $info,
		);	
// 		dump($date);die;
        $this->show(message::getJsonMsgStruct('1001', $date));//成功	
	}
}
