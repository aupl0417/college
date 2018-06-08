<?php

/**
 * 多级联动select
 * adads
 * 2015-12-21
 */

class select {
	//根据最后一级行业分类取出所有上级行业select及其option;$id:行业分类的code;$return: 1-返回options,2-返回数组,;
	public static function initIndOptions($id = '01', $selected = true, $return = 1){
		$db = new MySql();
		$sql = "SELECT 
				ind2.*, IF(ind.ind_code = ind2.ind_code, 1, 0) AS selected
				FROM 
				`t_industry` AS ind
				RIGHT JOIN
				`t_industry` AS ind2 ON ind2.ind_fkey = ind.ind_fkey
				WHERE 
				TRIM(TRAILING '00' FROM ind.ind_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM ind.ind_code)))";
		$Inds = $db->getAll($sql);
		if($Inds){
			$_Inds = [];
			foreach($Inds as $v){
				$_Inds[$v['ind_fkey']][] = array(
					'id'	   => $v['ind_id'],
					'code'	   => $v['ind_code'],
					'name'	   => $v['ind_name'],
					'fkey'	   => $v['ind_fkey'],
					'gdp'	   => $v['ind_gdp'],
					'selected' => $v['selected'],
				);
			}
			if($return == 2){
				return $_Inds;
			}else{
				$_options = [];
				foreach($_Inds as $key => $value){
					$_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
					$_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
				}
				return $_options;
			}
		}else{//如果没有,返回空值
			return ($return == 2) ? [] : '';
		}
		//dump($_options);
	}
	
	//获取下一级行业分类
	public static function getIndChildren($id = '01', $selected = true, $return = 1){
		$db = new MySql();
		$sql = "SELECT 
				ind2.*, ind.ind_id
				FROM 
				`t_industry` AS ind 
				RIGHT JOIN
				`t_industry` AS ind2
				ON ind2.ind_fkey = ind.ind_id
				WHERE ind.ind_code = '".$id."'";
		$Inds = $db->getAll($sql);
		if($Inds){
			$_Inds = [];
			foreach($Inds as $v){
				$_Inds[$v['ind_fkey']][] = array(
					'id'	   => $v['ind_id'],
					'code'	   => $v['ind_code'],
					'name'	   => $v['ind_name'],
					'fkey'	   => $v['ind_fkey'],
					'gdp'	   => $v['ind_gdp'],
					//'selected' => $v['selected'],
				);
			}
			if($return == 2){
				return $_Inds;
			}else{
				$_options = [];
				foreach($_Inds as $key => $value){
					$_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
					$_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
				}
				return $_options;
			}
		}else{//如果没有,返回空值
			return ($return == 2) ? [] : '';
		}
	}
	
	//根据最后一级行业分类取出完整行业路径 $id:行业分类的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
	public static function getFullInd($id = '', $return = 1, $split = ' &gt; '){
		$db = new MySql();
		$sql = "SELECT ind_id, ind_code, ind_name, ind_fkey, ind_gdp FROM `t_industry` WHERE TRIM(TRAILING '00' FROM ind_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM ind_code))) ORDER BY ind_code ASC";
		$Inds = $db->getAll($sql);
		if($Inds){
			$_Inds = '';
			foreach($Inds as $v){
				$_Inds[] = array(
					'id' => $v['ind_id'],
					'code' => $v['ind_code'],
					'name' => $v['ind_name'],
					'fkey' => $v['ind_fkey'],
					'gdp' => $v['ind_gdp'],
				);
			}
			if($return == 1){//返回数组
				return $_Inds;
			}else{
				return implode($split, array_column($_Inds, 'name'));
			}
		}else{//如果没有,返回空值
			return ($return == 1) ? [] : '';
		}
	}
	

	//根据最后一级地区分类取出所有上级地区select及其option;$id:地区的code;$return: 1-返回options,2-返回数组,;
	public static function initAreaOptions($id = '11', $selected = true, $return = 1){
		$db = new MySql();
		$sql = "SELECT 
				area2.*, IF(area.a_code = area2.a_code, 1, 0) AS selected
				FROM 
				`t_area` AS area
				RIGHT JOIN
				`t_area` AS area2 ON area2.a_fkey = area.a_fkey
				WHERE 
				LENGTH(area2.a_code) = 6 
				AND
				TRIM(TRAILING '00' FROM area.a_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM area.a_code)))";
		$Area = $db->getAll($sql);
		if($Area){
			$_Area = [];
			foreach($Area as $v){
				$_Area[$v['a_fkey']][] = array(
					'id'	   => $v['a_id'],
					'code'	   => $v['a_code'],
					'name'	   => $v['a_name'],
					'fkey'	   => $v['a_fkey'],
					'gdp'	   => $v['a_gdp'],
					'selected' => $v['selected'],
				);
			}
			if($return == 2){
				return $_Area;
			}else{
				$_options = [];
				foreach($_Area as $key => $value){
					$_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
					$_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
				}
				return $_options;
			}
		}else{//如果没有,返回空值
			return ($return == 2) ? [] : '';
		}
		//dump($_options);
	}
	
	//获取下一级地区分类
	public static function getAreaChildren($id = '01', $selected = true, $return = 1){
		$db = new MySql();
		$sql = "SELECT 
				area2.*, area.a_id
				FROM 
				`t_area` AS area 
				RIGHT JOIN
				`t_area` AS area2
				ON area2.a_fkey = area.a_id
				WHERE area.a_code = '".$id."'";
		$Area = $db->getAll($sql);
		if($Area){
			$_Area = [];
			foreach($Area as $v){
				$_Area[$v['a_fkey']][] = array(
					'id'	   => $v['a_id'],
					'code'	   => $v['a_code'],
					'name'	   => $v['a_name'],
					'fkey'	   => $v['a_fkey'],
					'gdp'	   => $v['a_gdp'],
					//'selected' => $v['selected'],
				);
			}
			if($return == 2){
				return $_Area;
			}else{
				$_options = [];
				foreach($_Area as $key => $value){
					$_selected = $selected ? array_search('1', array_column($value, 'selected', 'code')) : [];
					$_options[] = F::array2Options(array_column($value, 'name', 'code'), [$_selected]);
				}
				return $_options;
			}
		}else{//如果没有,返回空值
			return ($return == 2) ? [] : '';
		}
	}	
	
	//根据最后一级行政区划取出完整行政区划路径 $id:行政区划的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
	public static function getFullArea($id = '', $return = 1, $split = ' &gt; '){
		$db = new MySql();		
		$sql = "SELECT a_id, a_code, a_name, a_fkey, a_gdp FROM `t_area` WHERE TRIM(TRAILING '00' FROM a_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM a_code))) ORDER BY a_code ASC";
		$areas = $db->getAll($sql);
		if($areas){
			$_areas = '';
			foreach($areas as $v){
				$_areas[] = array(
					'id'   => $v['a_id'],
					'code' => $v['a_code'],
					'name' => $v['a_name'],
					'fkey' => $v['a_fkey'],
					'gdp'  => $v['a_gdp'],
				);
			}
			if($return == 1){//返回数组
				return $_areas;
			}else{
				return implode($split, array_column($_areas, 'name'));
			}
		}else{//如果没有,返回空值
			return ($return == 1) ? [] : '';
		}
	}

}

?>
