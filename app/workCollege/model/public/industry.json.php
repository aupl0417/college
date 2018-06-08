<?php
/* 
	通过行业编码取得下级联动
 */
class industry_json extends worker {

    function run() {
		$options = $this->options;
 		if(isset($options['id'])){
			$id = F::fmtNum($options['id']);
			$id = $id ? $id : 0;
		}else{
			$id = 0;
		}

		$db = new MySql();
		if($id == 0){//取出所有一级分类
			$sql = "select ind_code as code, ind_name as `name`, ind_id as id, SUBSTR(ind_code, 1, 2) as `key` from t_industry where ind_fkey=0";
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
					$sql = "select ind_code as code, ind_name as `name`, ind_id as id, SUBSTR(ind_code, 1, 4) as `key` from t_industry where ind_code like '".$id."%00' and ind_code<>'".$id."0000' ORDER BY `ind_code` ASC";
					break;
				
				case 4:
					$sql = "select ind_code as code, ind_name as `name`, ind_id as id, ind_code as `key` from t_industry where ind_code like '".$id."%' and ind_code<>'".$id."00' ORDER BY `ind_code` ASC";
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
