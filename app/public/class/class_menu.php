<?php

/**
 * 菜单读取类
 * adadsa
 * 2015-11-25
 */
class menu {
	
	protected $table = 'tang_power';
	  	
	//读取当前用户的左侧菜单并输出
	function getUserMenu($root = 1, $db = null){
		$db = $db ? $db : new MySql();
		
		$sql = "select p_id as id, p_name as `name`, p_url as url, p_isMenu as isMenu, p_isEnd as isEnd, p_fid as fid, p_showOrder as `order`, p_isBlank as isBlank from `".$this->table."` where  (p_root = '". $root ."' or p_fid = '0') and p_isMenu=1 ORDER BY fid ASC, p_showOrder ASC";//p_id in (". $userPowerList .") and and p_id in (". $userPowerList .") 
		//echo $sql;
		$data = $db->getAll($sql);
		$new = array();
		foreach ($data as $v){
			$new[$v['fid']][] = $v;
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
        
        //读取当前用户的左侧菜单并输出
	function getWorkMenu($root = 1, $db = null){
		$db = $db ? $db : new MySql();		
		//$userPowerList = $_SESSION['userPower'];			
		$sql = "select p_id as id, p_name as `name`, p_url as url, p_isMenu as isMenu, p_isEnd as isEnd, p_fid as fid, p_showOrder as `order`, p_root as `root` from `tang_power_work` where  p_isMenu=1 ORDER BY fid ASC, p_showOrder ASC";		//p_id in (". $userPowerList .")  and
		 
		$data = $db->getAll($sql);
		$new = array();
		foreach ($data as $v){
			$new[$v['fid']][] = $v;
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
	
	function createTree(&$list, $root){
		$tree = array();
		foreach ($root as $k=>$l){
			if($l['fid'] == 0){
				$url = $l['url'];
				if(in_array($l['url'], ['u', 'trade', 'pay', 'edu', 'www'])){
					$l['url'] = U($url.'/index/index', '', 1);
					if(in_array($url, ['u', 'trade'])){
						$l['target'] = 0;
					}
					else{
						$l['target'] = 1;
					}
				}else{
					$l['target'] = 1;
				};
			}
			if(isset($list[$l['id']])){
				$l['children'] = self::createTree($list, $list[$l['id']]);
			}
			$tree[] = $l;
		} 
		return $tree;
	}
	
	function getFullWorkMenu($flush = 0, $db = null){
        $cache = new cache();
        $_menu = $cache->get("fullWorkMenu");
        if ($flush || !$_menu) {
			$db = $db ? $db : new MySql();
			$sql = "SELECT p1.p_id, p1.p_name, (SELECT GROUP_CONCAT(p2.p_name SEPARATOR ' &gt; ') FROM `tang_power_work` AS p2 WHERE p2.p_id = SUBSTR(p1.p_id, 1, LENGTH(p2.p_id)) ORDER BY p2.p_fid ASC) AS p_fullName FROM tang_power_work AS p1";
			$data = $db->getAll($sql);			
			if($data){
				foreach($data as $v){
					$_menu[$v['p_id']] = $v;
				}
			}
			$cache->set("fullWorkMenu", $_menu);
		}
		return $_menu;
	}
	
	function getFullUserMenu($flush = 0, $db = null){
        $cache = new cache();
        $_menu = $cache->get("fullUserMenu");
        if ($flush || !$_menu) {
			$db = $db ? $db : new MySql();
			$sql = "SELECT p1.p_id, p1.p_name, (SELECT GROUP_CONCAT(p2.p_name SEPARATOR ' &gt; ') FROM `".$this->table."` AS p2 WHERE p2.p_id = SUBSTR(p1.p_id, 1, LENGTH(p2.p_id)) ORDER BY p2.p_fid ASC) AS p_fullName FROM `".$this->table."` AS p1";
			$data = $db->getAll($sql);			
			if($data){
				foreach($data as $v){
					$_menu[$v['p_id']] = $v;
				}
			}
			$cache->set("fullUserMenu", $_menu);
		}
		return $_menu;
	}
}

?>
