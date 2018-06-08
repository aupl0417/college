<?php

class load_json extends guest {

    function run() {
		$options = $this->options;
		
		$currentMenuId = isset($options['currentMenuId']) ? F::fmtNum($options['currentMenuId']) : 1;
		$currentMenuId = !$currentMenuId ? 1 : $currentMenuId;
		
		$result = apis::request('/college/api/publicLoad.json', ['currentMenuId' => $currentMenuId, 'userID' => $_SESSION['userID']], true);

		if(isset($result['code']) && $result['code'] == '1001'){//获取数据成功
		    $db = new MySql();
		    $identityType = $db->getField('select identityType from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"');
		    if($identityType == 0){
		        foreach($result['data']['menu']['menu'] as $key=>&$val){
		            if($val['id'] == 5){
                        if(is_array($val['children'])){
                            foreach($val['children'] as $k=>&$v){
                                if($v['id'] == '502'){
                                    unset($val['children'][$k]);
                                    continue 2;
                                }
                            }
                        }
		            }
		        }
		    }
			$this->show(message::getJsonMsgStruct('1001', $result['data']));
		}else{
			$this->show(message::getJsonMsgStruct('1002'));
		};
    }

}
