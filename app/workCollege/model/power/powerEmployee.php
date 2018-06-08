<?php

class powerEmployee extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [50]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		$id = isset($options['id']) ? $options['id'] : '';
// 		$level = isset($options['level']) ? (F::fmtNum($options['level']) - 0) : 0;
		

		if($id == '' ){//&& $level == 0
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误	
			exit;
		}
		
		$sql = "SELECT e_powerList as powerList FROM tang_employee WHERE e_id = '".$id."'";
			
		$result = $this->db->getField($sql);
		$selected = array_flip(explode(',', $result));
		
		$allPowerList = $this->getPowerList($selected);
		$allPowerList = json_encode($allPowerList);
		$info = array();
		$data = array(
			'jsData'	   => json_encode($info),
			'tempId'	   => 'temp_'.F::getGID(),
			'powerList'	   => $result,	
			'allPowerList' => $allPowerList,	
			'id'		   => $id,
			'powerlist'	   => $result
		);
// 		echo json_encode($this->getPowerList());die;
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
	
	function getPowerList($selected = array()){
		
		$userPowerList = $_SESSION['userPower'];			
		$sql = "select p_id as id, p_name as `text`, p_fid as fid, p_showOrder as `order`, p_root as `root` from `tang_power_work` where p_id>0 ORDER BY fid ASC, p_showOrder ASC";		//p_id in (". $userPowerList .")  and		 
		
		$data = $this->db->getAll($sql);
		//dump($data);
		$new = array();
		foreach ($data as $k => $v){
			if(array_key_exists($v['id'], $selected) === false){
				$data[$k]['state'] = array(
											'opened'=>false,
											'selected'=>false,
										  );
			}else{
				$data[$k]['state'] = array(
											'opened'=>true,
											'selected'=>true,
										  );				
			}
			$new[$v['fid']][] = $data[$k];
		}
		
		$root = array();
		foreach($data as $v){
			if($v['fid'] == 0){
				$root[] = $v;
			}
		}
	
		$tree = $this->createTree($new, $root);
		return $tree;
	}
	
	function createTree($list, $root){
		$tree = array();
		foreach ($root as $k=>$l){
			if(isset($list[$l['id']])){
				$l['state']['selected'] = false;
				$l['children'] = self::createTree($list, $list[$l['id']]);
			}
			$tree[] = $l;
		} 
		return $tree;
	}	
}
