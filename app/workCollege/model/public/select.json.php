<?php

class select_json extends worker {

    function run() {
		$options = $this->options;
		$type	  = isset($options['type']) ?  $options['type'] : 'region';
		$val	  = isset($options['val']) ? $options['val'] : '';
		$selected = isset($options['selected']) ? !!$options['selected'] : false;//初始化select时是否需要选中option
		$isParent = isset($options['isParent']) ? !!$options['isParent'] : false;//是否只取出下一级数据
		
		$return = '';

		switch($type){
			case 'industry':
				$val = $val == '' ? '01' : $val;				
				$return = $isParent ? attrib::getIndChildren($val, $selected) : attrib::initIndOptions($val, $selected);
				break;
			case 'region':
				$val = $val == '' ? '11' : $val;				
				$return = $isParent ? attrib::getAreaChildren($val, $selected) : attrib::initAreaOptions($val, $selected);
				break;		
			case 'org'://部门
				$val = $val == '' ? '11' : $val;				
				$return = $isParent ? attrib::getOrgChildren($val, $selected) : attrib::initOrgOptions($val, $selected);
				break;
			case 'flow'://工单
				$val = $val == '' ? '0' : $val;				
				$return = $isParent ? attrib::getFlowChildren($val, $selected) : attrib::getFlowChildren($val, $selected);
				break;	
			case 'employee'://员工
				$orgs = isset($options['orgs']) ? $options['orgs'] : '';
				$val = isset($options['val']) ? $options['val'] : '';;
				$return = attrib::getEmployeeByOrgs($orgs, $val);
				break;
			case 'orgselected'://已选择部门
				$return = attrib::getOrgSelected($val);
				break;
			default:
				
				break;
		}
		$this->show(message::getJsonMsgStruct('1001', $return));
		
    }

}
