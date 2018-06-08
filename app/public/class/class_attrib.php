<?php

/**
 * 各种属性的集中处理
 *
 * @author flybug
 * @version 1.0.0
 */
class attrib {
	static private $cacheTime = 86400;
    //系统参数的获取
    static public function getSystemParaByKey($key, $flush = 0, $db = NULL) {
        $para = [];
        $cache = new cache();
        if(!$flush){
            $para = $cache->get("systempara.$key");
        }
        if (!$para) {
            $db = is_null($db) ? new MySql() : $db;
			
            $ret = $db->getAll('SELECT sys_key,sys_value FROM t_system');
            foreach ($ret as $v) {
                $cache->set("systempara.{$v['sys_key']}", $v['sys_value']);
            }
            $para = $cache->get("systempara.$key");
        }
        return $para;
    }
	
	//全部系统参数获取
	static public function getSystemParas($flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getSystemParas'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if (!$data) {
            $db = new MySql();
            $ret = $db->getAll('SELECT sys_key, sys_value FROM t_system');
            $data = array_column($ret, 'sys_value', 'sys_key');
            $cache->set($cacheKey, $data, self::$cacheTime);
            return $data;
		}

		return $data;
	}

    //系统参数的设置
    static public function setSystemParaByKey($key, $value, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('setSystemParaByKey'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            if ($db->update('t_system', ['sys_value'=>$value], "sys_key = '$key'") == 1) {
                self::getSystemParaByKey('discount_gplp',1);
                self::getSystemParas(1);
                $data = true;
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }else{
                $data = false;
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;
    }
		
	
	//推广系数参数获取
	static public function getPromotingFactor($key, $db = NULL, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = 'promotingFactor.'.$key;
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if (!$data) {
            $db = is_null($db) ? new MySql() : $db;
            $ret = $db->getAll("SELECT pf_id, pf_val FROM t_promoting_factor WHERE pf_type = '".$key."' ORDER BY pf_id DESC");
            $data = array_column($ret, 'pf_val', 'pf_id');
            $cache->set($cacheKey, $data, self::$cacheTime);
            return $data;
		}
		
		return $data;
	}
	
	//清除推广系数参数
	static public function clearPromotingFactor($key) {
        $cache = new cache();
        $cacheKey = 'promotingFactor.'.$key;
        
        $data = $cache->get($cacheKey);
		
		if($data){
			return $cache->del($cacheKey);
		}
		return true;
	}
	
	//根据订单号和推广系数类型取出对应的推广系数值
	static public function getPromotingFactorByOrder($key, $orderid = '', $db = NULL) {
		$params = self::getPromotingFactor($key, $db);
		if(!$params){
			return 0;
		}
		$orderid = ($orderid == '') ? date('YmdHis').'999' : substr($orderid, 0, 14).'999';
	
		foreach($params as $k => $v){
			if($k <= $orderid){
				return $v;
			}
		}
		return 0;
	}


    /* 行业相关 *********************************start******************************************************* */

    //根据最后一级行业分类取出所有上级行业select及其option;$id:行业分类的code;$return: 1-返回options,2-返回数组,;
    public static function initIndOptions($id = '01', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('initIndOptions'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data) {

            $id = $id == '0' ? '01' : $id;
            $db = new MySql();
            $sql = "SELECT
				ind2.*, IF(ind.ind_code = ind2.ind_code, 1, 0) AS selected
				FROM 
				`t_industry` AS ind
				RIGHT JOIN
				`t_industry` AS ind2 ON ind2.ind_fkey = ind.ind_fkey
				WHERE 
				TRIM(TRAILING '00' FROM ind.ind_code) = SUBSTR('" . $id . "', 1, LENGTH(TRIM(TRAILING '00' FROM ind.ind_code)))";
            $Inds = $db->getAll($sql);
            if ($Inds) {
                $_Inds = [];
                foreach ($Inds as $v) {
                    $_Inds[$v['ind_fkey']][] = array(
                        'id' => $v['ind_id'],
                        'code' => $v['ind_code'],
                        'name' => $v['ind_name'],
                        'fkey' => $v['ind_fkey'],
                        'gdp' => $v['ind_gdp'],
                        'selected' => $v['selected'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey,$_Inds,self::$cacheTime);
                    return $_Inds;
                } else {
                    $_options = [];
                    foreach ($_Inds as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
                    }
                    $cache->set($cacheKey,$_options,self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;

        //dump($_options);
    }

    //获取下一级行业分类
    public static function getIndChildren($id = '01', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getIndChildren'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            $sql = "SELECT
				ind2.*, ind.ind_id
				FROM
				`t_industry` AS ind
				RIGHT JOIN
				`t_industry` AS ind2
				ON ind2.ind_fkey = ind.ind_id
				WHERE ind.ind_code = '" . $id . "'";
            $Inds = $db->getAll($sql);
            if ($Inds) {
                $_Inds = [];
                foreach ($Inds as $v) {
                    $_Inds[$v['ind_fkey']][] = array(
                        'id' => $v['ind_id'],
                        'code' => $v['ind_code'],
                        'name' => $v['ind_name'],
                        'fkey' => $v['ind_fkey'],
                        'gdp' => $v['ind_gdp'],
                        //'selected' => $v['selected'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey, $_Inds, self::$cacheTime);
                    return $_Inds;
                } else {
                    $_options = [];
                    foreach ($_Inds as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
                    }
                    $cache->set($cacheKey, $_options, self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;
    }

    //根据最后一级行业分类取出完整行业路径 $id:行业分类的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
    public static function getFullInd($id = '', $return = 1, $split = ' &gt; ', $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getFullInd'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            $sql = "SELECT ind_id, ind_code, ind_name, ind_fkey, ind_gdp FROM `t_industry` WHERE TRIM(TRAILING '00' FROM ind_code) = SUBSTR('" . $id . "', 1, LENGTH(TRIM(TRAILING '00' FROM ind_code))) ORDER BY ind_code ASC";
            $Inds = $db->getAll($sql);
            if ($Inds) {
                $_Inds = '';
                foreach ($Inds as $v) {
                    $_Inds[] = array(
                        'id' => $v['ind_id'],
                        'code' => $v['ind_code'],
                        'name' => $v['ind_name'],
                        'fkey' => $v['ind_fkey'],
                        'gdp' => $v['ind_gdp'],
                    );
                }
                if ($return == 1) {//返回数组
                    $cache->set($cacheKey, $_Inds, self::$cacheTime);
                    return $_Inds;
                } else {
                    $data = implode($split,array_column($_Inds, 'name'));
                    $cache->set($cacheKey, $data, self::$cacheTime);
                    return $data;
                }
            } else {//如果没有,返回空值
                $data = ($return == 1) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;

    }

    /* 行业相关 *********************************end********************************************************* */


    /* 地区相关 *********************************start******************************************************* */

    //根据最后一级地区分类取出所有上级地区select及其option;$id:地区的code;$return: 1-返回options,2-返回数组,;
    public static function initAreaOptions($id = '11', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('initAreaOptions'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $id = $id == '0' ? '11' : $id;
            $db = new MySql();
            if(strlen($id) == 112 && substr($id, -3, 3) != '000'){//取 村/居委
                $areaId = substr($id, 0, 9);
                $aid = $areaId.'000';
                $sql = "SELECT
				area2.*, IF(area.a_code = area2.a_code, 1, 0) AS selected
				FROM
				`tang_area` AS area
				RIGHT JOIN
				`tang_area` AS area2 ON area2.a_fkey = area.a_fkey
				WHERE
				TRIM(TRAILING '00' FROM area.a_code) = SUBSTR('" . $aid . "', 1, LENGTH(TRIM(TRAILING '00' FROM area.a_code)))";
                $Area = $db->getAll($sql);

                $sql = "SELECT ae_id as a_id, ae_code as a_code, ae_name as a_name, ae_aid as a_fkey, ae_gdp as a_gdp, IF(ae_code = '".$id."', 1, 0) AS selected   FROM t_areaex WHERE ae_code LIKE '".$areaId."%';";

                $Areaex = $db->getAll($sql);
                $Area = array_merge($Area, $Areaex);
                //$sArr = array_column($Area, 'a_id', 'selected');
                //dump($Areaex);dump($Area);die;
            }else{//省/市/区/乡镇街道
                $sql = "SELECT
				area2.*, IF(area.a_code = area2.a_code, 1, 0) AS selected
				FROM
				`tang_area` AS area
				RIGHT JOIN
				`tang_area` AS area2 ON area2.a_fkey = area.a_fkey
				WHERE
				TRIM(TRAILING '00' FROM area.a_code) = SUBSTR('" . $id . "', 1, LENGTH(TRIM(TRAILING '00' FROM area.a_code)))";
                $Area = $db->getAll($sql);
            }

            /* LENGTH(area2.a_code) = 6
              AND */

            if ($Area) {
                $_Area = [];
                foreach ($Area as $v) {
                    $_Area[$v['a_fkey']][] = array(
                        'id' => $v['a_id'],
                        'code' => $v['a_code'],
                        'name' => $v['a_name'],
                        'fkey' => $v['a_fkey'],
                        'gdp' => $v['a_gdp'],
                        'selected' => $v['selected'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey, $_Area, self::$cacheTime);
                    return $_Area;
                } else {
                    $_options = [];
                    foreach ($_Area as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
                    }
                    $cache->set($cacheKey, $_options, self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
            //dump($_options);
        }

        return $data;
    }

    //获取下一级地区分类
    public static function getAreaChildren($id = '01', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getAreaChildren'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            if(strlen($id) == 112 && substr($id, -3, 3) == '000'){//取 村/居委
                $id = substr($id, 0, 9);
                $sql = "SELECT
				ae_id as a_id, ae_code as a_code, ae_name as a_name, ae_aid as a_fkey, ae_gdp as a_gdp
				FROM
				`tang_areaex`
				WHERE ae_code like '" . $id . "%'";
            }else{//省/市/区/乡镇街道
                $sql = "SELECT
				area2.*, area.a_id
				FROM
				`tang_area` AS area
				RIGHT JOIN
				`tang_area` AS area2
				ON area2.a_fkey = area.a_id
				WHERE area.a_code = '" . $id . "'";
            }

            $Area = $db->getAll($sql);
            if ($Area) {
                $_Area = [];
                foreach ($Area as $v) {
                    $_Area[$v['a_fkey']][] = array(
                        'id' => $v['a_id'],
                        'code' => $v['a_code'],
                        'name' => $v['a_name'],
                        'fkey' => $v['a_fkey'],
                        'gdp' => $v['a_gdp'],
                        //'selected' => $v['selected'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey, $_Area, self::$cacheTime);
                    return $_Area;
                } else {
                    $_options = [];
                    foreach ($_Area as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
                    }
                    $cache->set($cacheKey, $_options, self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;


    }

    //根据最后一级行政区划取出完整行政区划路径 $id:行政区划的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
    public static function getFullArea($id = '', $return = 1, $split = ' &gt; ', $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getFullArea'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            if(strlen($id) == 112 && substr($id, -3, 3) != '000'){//取 村/居委
                $aid = substr($id, 0, 9).'000';
                $sql = "SELECT a_id, a_code, a_name, a_fkey, a_gdp
					FROM
					`tang_area`
					WHERE TRIM(TRAILING '00' FROM a_code) = SUBSTR('".$aid."', 1, LENGTH(TRIM(TRAILING '00' FROM a_code))) AND LENGTH(a_code)=6
					UNION
					SELECT a_id, a_code, a_name, a_fkey, a_gdp
					FROM
					`tang_area`
					WHERE TRIM(TRAILING '000' FROM a_code) = SUBSTR('".$aid."', 1, LENGTH(TRIM(TRAILING '000' FROM a_code))) AND LENGTH(a_code)=12
					UNION
					SELECT ae_id AS a_id, ae_code AS a_code, ae_name AS a_name, ae_aid AS a_fkey, ae_gdp AS a_gdp
					FROM
					`tang_areaex`
					WHERE ae_code = '".$id."'
					ORDER BY a_code ASC";
            }else{
                $sql = "SELECT a_id, a_code, a_name, a_fkey, a_gdp FROM `tang_area` WHERE TRIM(TRAILING '00' FROM a_code) = SUBSTR('" . $id . "', 1, LENGTH(TRIM(TRAILING '00' FROM a_code))) ORDER BY a_code ASC";
            }
            $areas = $db->getAll($sql);
            if ($areas) {
                $_areas = '';
                foreach ($areas as $v) {
                    $_areas[] = array(
                        'id' => $v['a_id'],
                        'code' => $v['a_code'],
                        'name' => $v['a_name'],
                        'fkey' => $v['a_fkey'],
                        'gdp' => $v['a_gdp'],
                    );
                }
                if ($return == 1) {//返回数组
                    $cache->set($cacheKey, $_areas, self::$cacheTime);
                    return $_areas;
                } else {
                    $data = implode($split, array_column($_areas, 'name'));
                    $cache->set($cacheKey, $data, self::$cacheTime);
                    return $data;
                }
            } else {//如果没有,返回空值
                $data = ($return == 1) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;
    }

    /* 地区相关 *********************************end********************************************************* */


    /* 部门相关 *********************************start******************************************************* */

    //根据最后一级部门分类取出所有上级部门select及其option;$id:部门的code;$return: 1-返回options,2-返回数组,;
    public static function initOrgOptions($id = '11', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('initOrgOptions'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            $sql = "SELECT
				org2.*, IF(org.dm_code = org2.dm_code, 1, 0) AS selected
				FROM
				`t_organization` AS org
				RIGHT JOIN
				`t_organization` AS org2 ON org2.dm_fid = org.dm_fid
				WHERE
				org.dm_code =  SUBSTR('" . $id . "', 1, LENGTH(org.dm_code))
				ORDER BY org2.dm_code ASC";
            $Org = $db->getAll($sql);
            if ($Org) {
                $_Org = [];
                foreach ($Org as $v) {
                    $_Org[$v['dm_fid']][] = array(
                        'id' => $v['dm_id'],
                        'code' => $v['dm_code'],
                        'name' => $v['dm_name'],
                        'fid' => $v['dm_fid'],
                        'selected' => $v['selected'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey, $_Org, self::$cacheTime);
                    return $_Org;
                } else {
                    $_options = [];
                    foreach ($_Org as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
                    }
                    $cache->set($cacheKey, $_options, self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
            //dump($_options);
        }

        return $data;
    }

    //获取下一级部门分类
    public static function getOrgChildren($id = '11', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getOrgChildren'.json_encode(func_get_args()));
        $cache->del($cacheKey);
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            $sql = "SELECT
				org2.*
				FROM
				`t_organization` AS org
				RIGHT JOIN
				`t_organization` AS org2
				ON org2.dm_fid = org.dm_id
				WHERE org.dm_code = '" . $id . "'";
            $Area = $db->getAll($sql);
            if ($Area) {
                $_Area = [];
                foreach ($Area as $v) {
                    $_Area[$v['dm_fid']][] = array(
                        'id' => $v['dm_id'],
                        'code' => $v['dm_code'],
                        'name' => $v['dm_name'],
                        'fid' => $v['dm_fid'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey, $_Area, self::$cacheTime);
                    return $_Area;
                } else {
                    $_options = [];
                    foreach ($_Area as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
                    }
                    $cache->set($cacheKey, $_options, self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;
    }
	
    //已选择部门
    public static function getOrgSelected($selected, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getOrgSelected'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $selected = F::addYh($selected);
            $db = new MySql();
            $sql = "SELECT dm_id as val, dm_name as text FROM t_organization WHERE dm_id IN (".$selected.")";
            $data = $db->getAll($sql);
            $cache->set($cacheKey, $data, self::$cacheTime);
            return $data;
        }

        return $data;
		
	}	

    //根据最后一级部门取出完整行部门路径 $id:部门的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
    public static function getFullOrg($id = '', $return = 1, $split = ' &gt; ', $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getFullOrg'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            $sql = "SELECT
				org.*
				FROM
				`t_organization` AS org
				WHERE
				org.dm_code =  SUBSTR('" . $id . "', 1, LENGTH(org.dm_code))
				ORDER BY org.dm_code ASC";
            $Org = $db->getAll($sql);
            if ($Org) {
                $_Org = '';
                foreach ($Org as $v) {
                    $_Org[] = array(
                        'id' => $v['dm_id'],
                        'code' => $v['dm_code'],
                        'name' => $v['dm_name'],
                        'fid' => $v['dm_fid'],
                    );
                }
                if ($return == 1) {//返回数组
                    $cache->set($cacheKey, $_Org, self::$cacheTime);
                    return $_Org;
                } else {
                    $data = implode($split, array_column($_Org, 'name'));
                    $cache->set($cacheKey, $data, self::$cacheTime);
                    return $data;
                }
            } else {//如果没有,返回空值
                $data = ($return == 1) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;
    }
	
	//根据部门选择出所属员工
	/* 
	$orgs:部门id;
	$return: 返回类型 0-数组;1-options;
	$directly 部门直属员工;
	*/
	public static function getEmployeeByOrgs($orgs, $selected = '', $return = 1, $directly = true, $flush = 0){
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getEmployeeByOrgs'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            if(!is_array($selected)){
                $selected = explode(',', $selected);
            }

            $orgs = F::addYh($orgs);
            if($directly){//只取部门直属员工
                $sql = "SELECT e_id, e_name FROM t_employee WHERE e_departmentID IN (".$orgs.")";
            }else{//取出部门直属员工以及下级部门的员工
                $sql = "SELECT emp.e_id, emp.e_name FROM
					t_employee AS emp,
					t_organization AS org1,
					t_organization AS org2
					WHERE org2.dm_id IN(".$orgs.") AND org1.dm_code LIKE CONCAT(org2.dm_code,'%') AND emp.e_departmentID = org1.dm_id";
            }

            $result = $db->getAll($sql);

            if($return){
                $data = F::array2Options(array_column($result, 'e_name', 'e_id'), $selected);
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }else{
                $cache->set($cacheKey, $result, self::$cacheTime);
                return $result;
            }
        }

        return $data;
	}

    /* 部门相关 *********************************end********************************************************* */
	
	
	/* 工单分类相关 *********************************start********************************************************* */
    public static function getFlowChildren($id = '0', $selected = true, $return = 1, $flush = 0) {
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getFlowChildren'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            $sql = "SELECT * FROM t_flow WHERE flow_fid = '" . $id . "'";
            $Flows = $db->getAll($sql);
            if ($Flows) {
                $_Flows = [];
                foreach ($Flows as $v) {
                    $_Flows[$v['flow_fid']][] = array(
                        'id'	 => $v['flow_id'],
                        'name'	 => $v['flow_name'],
                        'fid'	 => $v['flow_fid'],
                    );
                }
                if ($return == 2) {
                    $cache->set($cacheKey, $_Flows, self::$cacheTime);
                    return $_Flows;
                } else {
                    $_options = [];
                    foreach ($_Flows as $key => $value) {
                        $_selected = $selected ? array_search('1', array_column($value, 'selected', 'id')) : [];
                        $_options[] = F::array2Options(array_column($value, 'name', 'id'), [$_selected]);
                    }
                    $cache->set($cacheKey, $_options, self::$cacheTime);
                    return $_options;
                }
            } else {//如果没有,返回空值
                $data = ($return == 2) ? [] : '';
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }
        }

        return $data;
    }
	
	/* 工单分类相关 *********************************end*********************************************************** */
	


    /* 获取购买代理区域的等级及该代理的价格***************************************************************************/
    public static function getGpLpProductDataUpdate($code, $flush = 0){
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getGpLpProductDataUpdate'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            if(strlen($code) == 12 && substr($code, -3, 3) != '000'){ //村、村委会
                $sql = "select ae_code as a_code, ae_gdp as a_gdp, ae_level as a_level from tang_areaex where ae_code = '".$code."'";
            }else{                                                   //省、市、区/县、镇/街道
                $sql = "select a_code,a_gdp,a_level,a_isBuy from tang_area where a_code = '".$code."'";
            }
            $GDP = $db->getRow($sql);

            if(isset($GDP['a_isBuy'])){
                if($GDP['a_isBuy'] == 0){
                    $data = false;
                    $cache->set($cacheKey, $data, self::$cacheTime);
                    return $data;
                }
            }

            if(!$GDP){
                $data = false;
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }

            if(!isset($GDP['a_gdp']) || $GDP['a_gdp'] == 0){      //没有GDP的值，则返回相对级别系数为1的GPLP价格
                if(!isset($GDP['a_level'])){
                    $data = false;
                    $cache->set($cacheKey, $data, self::$cacheTime);
                    return $data;
                }

                //1~7 级GPLP价格默认为大盘
                $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice, s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level  where p.cp_relativeLevel = '".$GDP['a_level']."' and p.cp_relativeCoefficient = '1' ";

                $baseRow = $db->getRow($sql);
                $cache->set($cacheKey, $baseRow, self::$cacheTime);
                return $baseRow;
            }else{
                $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice, p.cp_minValue,s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level order by p.cp_id desc limit 0,1";
                $minRow = $db->getRow($sql);

                $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice,p.cp_maxValue, s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level order by cp_id asc limit 0,1";
                $maxRow = $db->getRow($sql);

                if($GDP['a_gdp'] > $maxRow['cp_maxValue']){  //GDP大于最大maxValue值，GPLP取最大maxValue区间价格
                    $cache->set($cacheKey, $maxRow, self::$cacheTime);
                    return $maxRow;
                }elseif($GDP['a_gdp'] < $minRow['cp_minValue']){     //GDP小于最小minValue值，GPLP取最小minValue区间价格
                    $cache->set($cacheKey, $minRow, self::$cacheTime);
                    return $minRow;
                }else{
                    //1~7 级GPLP价格默认为大盘
                    $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice,s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level where p.cp_minValue <= ".$GDP['a_gdp']." and p.cp_maxValue >= ".$GDP['a_gdp'];
                    $result = $db->getRow($sql);
                    $cache->set($cacheKey, $result, self::$cacheTime);
                    return $result;
                }
            }
        }

        return $data;
    }

    /**
     * @author jojojing
     * 获取购买代理区域的等级及该代理的价格
     * @param $code         区域代码  必传
     * @param int $flush    是否刷新（0否，1是）
     * @return array|bool
     */
    /* 获取购买代理区域的等级及该代理的价格***************************************************************************/
    public static function getGpLpProductData($code, $flush = 0){
        $data = [];
        $cache = new cache();
        $cacheKey = md5('getGpLpProductData'.json_encode(func_get_args()));
        if(!$flush){
            $data = $cache->get($cacheKey);
        }
        if(!$data){
            $db = new MySql();
            if(strlen($code) == 12 && substr($code, -3, 3) != '000'){ //村、村委会
                $sql = "select ae_code as a_code, ae_gdp as a_gdp, ae_level as a_level from tang_areaex where ae_code = '".$code."'";
            }else{                                                   //省、市、区/县、镇/街道
                $sql = "select a_code,a_gdp,a_level,a_isBuy from tang_area where a_code = '".$code."'";
            }
            $GDP = $db->getRow($sql);   //获取当前区域的代码，社会消费品零售总额，规模等级

            //直辖区，县不可购买，a_isBuy=0不可买，a_isBuy=1可购买
            if(isset($GDP['a_isBuy'])){
                if($GDP['a_isBuy'] == 0){
                    $data = false;
                    $cache->set($cacheKey, $data, self::$cacheTime);
                    return $data;
                }
            }

            //无对应code记录
            if(!$GDP){
                $data = false;
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }

            //社会消费品零售总额不存在或未空，不可购买，返回false
            if(!isset($GDP['a_gdp']) || $GDP['a_gdp'] == 0){
                $data = false;
                $cache->set($cacheKey, $data, self::$cacheTime);
                return $data;
            }

            //代理表中最小值
            $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice, p.cp_minValue,s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level order by p.cp_id desc limit 0,1";
            $minRow = $db->getRow($sql);

            //代理表中最大值
            $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice,p.cp_maxValue, s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level order by cp_id asc limit 0,1";
            $maxRow = $db->getRow($sql);

            //1~7级GPLP价格，默认为大盘【通过该区域的GDP值所在区间】，判断其价格
            if($GDP['a_gdp'] > $maxRow['cp_maxValue']){  //GDP大于最大maxValue值，GPLP取最大maxValue区间价格
                //上行增长系数
                $seriNul = ceil(($GDP['a_gdp'] - $maxRow['cp_maxValue'])/10000);
                //相对基价
                $relativePrice = $maxRow['cp_relativeBaseMomeny']+$seriNul*0.5;

                $maxRow['cp_relativeBaseMomeny'] = $relativePrice;
                $maxRow['EPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$maxRow['cp_baseValue'],$relativePrice,6);
                $maxRow['DPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$maxRow['cp_baseValue']*2,$relativePrice,6);
                $maxRow['CPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$maxRow['cp_baseValue']*3,$relativePrice,6);
                $maxRow['BPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$maxRow['cp_baseValue']*4,$relativePrice,6);
                $maxRow['APrice'] = F::bankerAlgorithm($GDP['a_gdp']/$maxRow['cp_baseValue']*5,$relativePrice,6);
                $maxRow['FPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$maxRow['cp_baseValue']*3,$relativePrice,6);

                $cache->set($cacheKey, $maxRow, self::$cacheTime);
                return $maxRow;
            }elseif($GDP['a_gdp'] < $minRow['cp_minValue']){     //GDP小于最小minValue值，GPLP取最小minValue区间价格
                $cache->set($cacheKey, $minRow, self::$cacheTime);
                return $minRow;
            }else{
                //1~7 级GPLP价格默认为大盘
                $sql = "select p.cp_id,p.cp_level,p.cp_baseValue,p.cp_relativeLevel,p.cp_relativeCoefficient,p.cp_baseMomeny,p.cp_relativeBaseMomeny,p.cp_EPrice1 as EPrice,p.cp_DPrice1 as DPrice,p.cp_CPrice1 as CPrice,p.cp_BPrice1 as BPrice,p.cp_APrice1 as APrice,p.cp_FPrice1 as FPrice,s.cs_ANum1 as aNum, s.cs_BNum1 as bNum, s.cs_CNum1 as cNum,s.cs_DNum1 as dNum,s.cs_FNum1 as fNum, s.cs_ENum1 as lp from t_company_product as p left join t_company_struct as s on p.cp_relativeLevel = s.cs_level where p.cp_minValue <= ".$GDP['a_gdp']." and p.cp_maxValue >= ".$GDP['a_gdp'];
                $result = $db->getRow($sql);
                $result['EPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$result['cp_baseValue'],$result['cp_relativeBaseMomeny'],6);
                $result['DPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$result['cp_baseValue']*2,$result['cp_relativeBaseMomeny'],6);
                $result['CPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$result['cp_baseValue']*3,$result['cp_relativeBaseMomeny'],6);
                $result['BPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$result['cp_baseValue']*4,$result['cp_relativeBaseMomeny'],6);
                $result['APrice'] = F::bankerAlgorithm($GDP['a_gdp']/$result['cp_baseValue']*5,$result['cp_relativeBaseMomeny'],6);
                $result['FPrice'] = F::bankerAlgorithm($GDP['a_gdp']/$result['cp_baseValue']*3,$result['cp_relativeBaseMomeny'],6);
                $cache->set($cacheKey, $result, self::$cacheTime);
                return $result;
            }

        }

        return $data;
    }


	

    /**
     * $businessId  交易类型id,多个用数组表示：如array('101','102');
	 * $isLimitCustomer   1只返回客户端的，0不限制
	 * $selected 表示选中项目，请填写交易类型id
     * $returntType 返回类型 0 html格式，1数组    
     */
    static public function getBusinessType($businessId, $returntType=0,$isLimitCustomer=1,$selected="",$flush = 0) {
		$data = [];
        $cache = new cache();
        $cacheKey = md5('getBusinessType'.json_encode(func_get_args()));
		$where = [];
		$db = new MySql();
		if(empty($businessId)) {
			return false;	
		}
		if(!$flush){
           $data = $cache->get($cacheKey);
		   if(!empty($data))
			 return $data;
        }
		$isSingleBusinessId = false;
		if(is_numeric($businessId)){
		   if(strlen((string)$businessId) > 3) {
			   $sql = "SELECT bu_id,bu_name FROM t_business WHERE bu_id ='".$businessId."'";
			   $row = $db->getRow($sql);
			   $cache->set($cacheKey,$row); 
			   return $row;
		   }
		   $businessId = (array)$businessId;
		}
	    $tempArr = [];
		foreach($businessId as $key=>$val) {
			$val = (string)$val;
			$tempArr[] = 'bu_id like "'. $val.'%"';
		}
		$where[] = '('.implode(' or ',$tempArr).')';
		if($isLimitCustomer == 1) {
			$where[] = 'bu_show = 1';
		}
		$whereStr = implode(' and ',$where);
		$sql = "SELECT bu_id,bu_name FROM t_business WHERE $whereStr order by bu_id asc";
		$businesses =  $db->getAll($sql);
		$groups = [];
		foreach($businesses as $val) {
			   $groups[substr($val['bu_id'],0,3)][] = ['bu_id'=>$val['bu_id'],'bu_name'=>$val['bu_name']];
		} 
		if($returntType==1) {
		  $cache->set($cacheKey,$groups);
		  return $groups;
	    }
		$html = '';
		foreach($groups as $key=>$vals) {
			list($index,$firstArr) = each($vals);
			$html .=  count($groups) > 1 ? '<optgroup label="'.$firstArr['bu_name'].'">' : '';
			unset($vals[$index]);
			foreach($vals as $val) {
				$html .= '<option value ="'.$val['bu_id'].'" '.($selected!='' && $val['bu_id']  == $selected ? 'selected="selected"' : '').'>'.$val['bu_name'].'</option>';
			}
			$html .=  count($groups) > 1 ? '</optgroup>' : '';
		}
		$cache->set($cacheKey,$html);
		return $html;
		
    }



    static public function getBusinessKeyValue() {
        $db = new MySql();
        $sql = "select  bu_id,bu_name from t_business";
        $businesses =  $db->getAll($sql);

        $temp = array();
        foreach($businesses as $val) {
            $temp[$val['bu_id']] = $val['bu_name'];
        }

        return $temp;
    }
    /*通过代理等级获取规模*/
    static public function getCompanyStructSum($level){
        $cache = new cache();
        $struct = $cache->get($level);
        if(!$struct){
            $db = new MySql();
            $sql = "SELECT * FROM `t_company_struct` WHERE cs_level = '".$level."'";
            $struct = $db->getRow($sql);
            $cache->set($level,$struct,3600);
        }
        return $struct;
    }

    //获取员工用于select选项
    static function getEmployee(){
        //$options = $this->options;
        $db        = new MySql();
        $cache     = new cache();
        $search    = isset($options['search']) ?  $options['search'] : '';
        $cacheKey  = 'employees4select2';
        $result    = $cache->get($cacheKey);

        if(!$result){
            $sql = "SELECT p.dm_code AS pcode, p.dm_name AS pname, o.dm_code AS dcode, o.dm_name AS dname, e.e_id AS eid, e.e_name AS ename, d.dt_name AS duty, e.e_charName AS eChar FROM t_employee AS e 
                LEFT JOIN 
                t_organization AS o
			ON e.e_departmentID = o.dm_id
			LEFT JOIN
			t_organization AS p
			ON SUBSTR(o.dm_code FROM 1 FOR 4) = p.dm_code
			LEFT JOIN
			t_duty AS d
			ON e.e_dutyID=d.dt_id
			ORDER BY eChar ASC";
			$result = $db->getAll($sql);
			if(!$result){
                return [];
			}
			$cache->set($cacheKey, $result);			
		}
		
		
		$employees = [];
		foreach($result as $v){
			$employees[$v['pcode']]['id'] = $v['pcode'];
			$employees[$v['pcode']]['text'] = $v['pname'];
			$employees[$v['pcode']]['children'][] = [
				'id'	=> $v['eid'],
				'name'	=> $v['ename'],
				'eChar'	=> $v['eChar'],
				'duty'	=> $v['duty'],
				'dname'	=> $v['dname'],
			];
		}
		
		$html = '';
		foreach($employees as $o){
			if(isset($o['children'])){//optgroupoptgroup
                $html .= '<optgroup label="' . $o['text'] . '">';
                foreach ($o['children'] as $v) {
                    $html .= '<option value="' . $v['id'] . '"';
                    //$html .= (in_array($k, $selected)) ? ' selected="selected"' : '';
					$html .= ' data-char="' . $v['eChar'] . '"';
					$html .= ' data-duty="' . $v['duty'] . '"';
					$html .= ' data-dname="' . $v['dname'] . '"';
                    $html .= '>' . $v['name'] . '</option>';
                }
                $html .= '</optgroup>';				
			}else{//option
                $html .= '<option value="' . $v['id'] . '"';
                //$html .= (in_array($key, $selected)) ? ' selected="selected"' : '';
                $html .= '>' . $v['name'] . '</option>';
				
			}
		}

		return $html;
    }	
}

?>
