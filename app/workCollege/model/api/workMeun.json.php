<?php

class workMeun_json extends api {

    function run() {
		
		$root = 0;
		$menu = new menu();
		$sidebar = $menu->getWorkMenu($root);
		$result = array(
			'menu' => $sidebar,
			'domain' => DOMAIN,
			'subdomain' => SUBDOMAIN,
			'currentSubdomain' => APP_NAME,
			'root' => $root
		);
	
		return apis::apiCallback('1001', $result); 	
    }

}
