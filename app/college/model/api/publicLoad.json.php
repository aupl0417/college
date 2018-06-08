<?php
//页面底部公共调用文件,用于获取左侧菜单、页面导航、站内信信息
/* 
	
	@param userID    		用户ID             			  字符
	@param currentMenuId    当前页面的权限ID             数字
	
	开发人员: adadsa
	
 */
class publicLoad_json extends api {

    function run() {
		$options = $this->options;
		$userID = isset($options['userID']) ? trim($options['userID']) : '';
		
		if(!$userID || strlen($userID) != 32){
			return apis::apiCallback('1002', '参数错误');
		}
		//F::clearRegistClassPath();
		$db = new MySql();
		$cache = new cache();
		$cacheKey = 'userInfo'.$userID;
		$cache->set($cacheKey, null);
		//会员信息
		$userInfo = $cache->get($cacheKey);
		if(!$userInfo){
			$userInfo = apis::request('college/api/getUserInfo.json', ['userId' => $userID], true);
			$userInfo = $userInfo['data'];
			if(!$userInfo){
				return apis::apiCallback('1002', '参数错误');
			}
			
			if(!$userInfo['avatar']){
				$userInfo['logo'] = '/app/public/assets/images/user.png';
			}else{
                $userInfo['logo'] = $userInfo['avatar'];
            }
			
			$cache->set($cacheKey, $userInfo);
		}
		
		
		$currentMenuId = isset($options['currentMenuId']) ? F::fmtNum($options['currentMenuId']) : 1;
		$currentMenuId = !$currentMenuId ? 1 : $currentMenuId;
		
		//侧边栏菜单
		$root = substr($currentMenuId, 0, 1);
		$cacheKey = 'menu'.$root;
		$cache->set($cacheKey, null);
		$menu = $cache->get($cacheKey);
		if(!$menu || 1){
			$menu = new menu($root, $db);
			$sidebar = $menu->getUserMenu($root);
			$menu = array(
				'menu' => $sidebar,
				'domain' => DOMAIN,
				'subdomain' => SUBDOMAIN,
				'currentSubdomain' => APP_NAME,
				'root' => $root
			);
			$cache->set($cacheKey, $menu);
		}
		
		
		//面包屑导航
		$cacheKey = 'breadcrumb'.$currentMenuId;
		// $cache->set($cacheKey, null);
		$_urhere = $cache->get($cacheKey);
		if(!$_urhere || 1){
			$webUrl = U('/index');
			$sql = "SELECT * FROM `tang_power` WHERE p_id = SUBSTR('".$currentMenuId."', 1, LENGTH(p_id)) ORDER BY p_fid asc";
			$data = $db->getAll($sql);
			$_urhere = '<li> <i class="fa fa-home"></i> <a href="'.$webUrl.'" id="head">首页</a> </li>';
			$_count = count($data) - 1;
			foreach ($data as $k=>$val){
				if($k == $_count){
					$_urhere .= '<li> '.$val['p_name'].'</li>';
				}else{
					if($val['p_url'] == "u"){
					   $val['p_url'] = "";
					}
					$_class = $val['p_fid'] == 0 ? '' : ' class="ajaxify"';
					$_urhere .= '<li> <a href="'.$val['p_url'].'"'.$_class.'>'.$val['p_name'].'</a> </li>';
				}
			}
			$cache->set($cacheKey, $_urhere);
		}
        
        //echo '<pre>';
        //print_r($messageList);exit;
		$result = [
			'menu'			 => $menu,
			'breadcrumb'	 => $_urhere,
			'userInfo'		 => $userInfo
		];
		
		return apis::apiCallback('1001', $result);
    }
	
	

	//格式化时间
	private function format_date($time) {
		$nowtime = time();
		$difference = $nowtime - strtotime($time);

		switch ($difference) {

			case $difference <= '60' :
				$msg = '刚刚';
				break;

			case $difference > '60' && $difference <= '3600' :
				$msg = floor($difference / 60) . '分钟前';
				break;

			case $difference > '3600' && $difference <= '86400' :
				$msg = floor($difference / 3600) . '小时前';
				break;

			case $difference > '86400' && $difference <= '2592000' :
				$msg = floor($difference / 86400) . '天前';
				break;

			case $difference > '2592000' &&  $difference <= '7776000':
				$msg = floor($difference / 2592000) . '个月前';
				break;
			case $difference > '7776000':
				$msg = '很久以前';
				break;
		}

		return $msg;
	}

}
