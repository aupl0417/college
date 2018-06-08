<?php

/**
 * 用户分组类
 * adads
 * 2015-11-24
 */
class group {
	
	//通过权限id获取权限
	function getGroupPower($groupID, $db = null){
		$db = is_null($db) ? new MySql : $db;
		$row = $db->getRow("select g_powerList, g_powerHash from t_group where g_id = '".$groupID."'");
		if(!$row){
			return '';//权限不存在
		}
		
		$powerList = $row['g_powerList'];
		$powerHash = $row['g_powerHash'];
		if(!F::checkPowerHash($powerList, $powerHash)){
			return '';//权限被篡改
		}
		return $powerList;
	}
	
	//获取会员的默认权限id
	function getMemberDefault($db = null){
		$db = is_null($db) ? new MySql : $db;
		$id = $db->getField("select g_id from t_group where g_isMemberDefault = 1");
		return $id ? $id : 0;		
	}
	
	//获取雇员的默认权限id
	function getWorkerDefault($db = null){
		$db = is_null($db) ? new MySql : $db;
		$id = $db->getField("select g_id from t_group where g_isWorkerDefault = 1");
		return $id ? $id : 0;		
	}

}

?>