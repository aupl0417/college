<?php
/* 
	通过地区编码取得下级联动
 */
class area_json extends worker {

    function run() {
		$options = $this->options;
 		if(isset($options['id'])){
			$id = F::fmtNum($options['id']);
			$id = $id ? $id : 0;
		}else{
			$id = 0;
		}

		$db = new MySql();
		if($id == 0){//取出所有省级
			$sql = "select a_code as code, a_name as `name`, a_id as id, SUBSTR(a_code, 1, 2) as `key` from tang_area where a_fkey=0 and LENGTH(a_code) = 6";
			$result = $db->getAll($sql);
			if($result){
				//$result = array_columns($result, 'name', 'key');
				$area = array();
				foreach($result as $v){
					$area[$v['key']] = array('name'	   => $v['name']);
				}
				$result = $area;
			}else{
				$this->show(message::getJsonMsgStruct('1002', '参数错误'));
				exit;
			}
			
		}
		else{
			switch(strlen($id)){
				case 2:
					$sql = "select a_code as code, a_name as `name`, a_id as id, SUBSTR(a_code, 1, 4) as `key` from t_area where a_code like '".$id."%00' and LENGTH(a_code) = 6 and a_code<>'".$id."0000' ORDER BY `a_code` ASC";
					break;
				
				case 4:
					$sql = "select a_code as code, a_name as `name`, a_id as id, a_code as `key` from t_area where a_code like '".$id."%' and LENGTH(a_code) = 6 and a_code<>'".$id."00' ORDER BY `a_code` ASC";
					break;
				default:
					$this->show(message::getJsonMsgStruct('1002', '参数错误'));
					exit;					
					break;				
			}
		
			$result = $db->getAll($sql);
			
			if($result){
				$area = array();
				foreach($result as $v){
					$area[$v['key']] = array('name'	   => $v['name']);
				}
				$result = $area;				
			}else{
				$this->show(message::getJsonMsgStruct('1002', '参数错误'));
				exit;				
			};
		}
		
		
		$this->show(message::getJsonMsgStruct('1001', $result));
    }

}
