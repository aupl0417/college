<?php

class menu_json extends guest {

    function run() {
		$menu = apis::request('/workcollege/api/workMeun.json', '', true);
		if($menu['code'] == '1001'){
			$this->show(message::getJsonMsgStruct('1001', $menu['data']));
		}
    }

}
