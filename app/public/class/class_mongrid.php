<?php

/* ClassName: mongrid
 * Memo:基于mongodb的datatable读取类

 * */

class mongrid
{
	static $db = null;

    public function __construct() {
        self::$db = new mgdb();
    }	
	
	static function limit ( $request, $columns, $maxRecordset = 10000, $maxPre = 100)
	{
		$limit = '';
		if ( isset($request['start']) && $request['length'] != -1 ) {
			$request['start']  = intval($request['start']);
			$request['length'] = intval($request['length']);
			$request['length'] = ($request['length'] > $maxPre) ? $maxPre : $request['length'];//每页最大显示数量
			$request['start']  = ($request['start'] + $request['length'] > $maxRecordset) ? $maxRecordset - $request['length'] : $request['start'];//最多显示的记录			
		}else{
			$request['start']  = 0;
			$request['length'] = $maxPre;			
		}
		$limit = "LIMIT ".$request['start'].", ".$request['length'];
		return $limit;
	}
	
	static function order ( $request, $columns )
	{
		$order = '';
		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();			

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				
				$columnIdx = intval($request['order'][$i]['column']);
				
				$requestColumn = $request['columns'][$columnIdx];

				$field = array_key_exists( $requestColumn['data'], $columns ) ? $columns[$requestColumn['data']] : ($requestColumn['data'] == 'DT_RowId') ? 1 : $requestColumn['data'];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = ''.$field.' '.$dir;
				}
			}
			if($orderBy){
				$order = 'ORDER BY '.implode(', ', $orderBy);
			}
		}

		return $order;
	}
	
	static function filter ( $request, $columns ,$where='')
	{
		$globalSearch = array();
		
		if(isset($request['search']) && is_array($request['search'])){
			foreach($request['search'] as $key => $search){				
				$field = array_key_exists($key, $columns) ? $columns[$key] : $key;//如果有别名,替换出
				if(isset($search['filter'])){//单个查询条件
					switch($search['filter']){//eq/like/gt/lt
						case 'like' :
							$globalSearch[] = "".$field." LIKE '".$search['value']."%'";
							break;
						case 'gt' :	
							if(is_numeric($search['value'])){
								$globalSearch[] = "".$field." > ". F::fmtNum($search['value']) ."";
							}
							else{
								$globalSearch[] = "".$field." > '". $search['value'] ."'";
							}
							break;
						case 'lt' :
							if(is_numeric($search['value'])){
								$globalSearch[] = "".$field." < ". F::fmtNum($search['value']) ."";
							}
							else{
								$globalSearch[] = "".$field." < '". $search['value'] ."'";
							}
							break;
						case 'gte' :	
							if(is_numeric($search['value'])){
								$globalSearch[] = "".$field." >= ". F::fmtNum($search['value']) ."";
							}
							else{
								$globalSearch[] = "".$field." >= '". $search['value'] ."'";
							}
							break;
						case 'lte' :
							if(is_numeric($search['value'])){
								$globalSearch[] = "".$field." <= ". F::fmtNum($search['value']) ."";
							}
							else{
								$globalSearch[] = "".$field." <= '". $search['value'] ."'";
							}
							break;					
						case 'eq' :
							$globalSearch[] = "".$field." = '".$search['value']."'";
							break;			
						case 'in' :
							$globalSearch[] = "".$field." in (".$search['value'].")";
							break;
						default :
							break;
					}		
				}else{//多个查询条件
					$fieldSearch = array();
					foreach($search as $k => $sch){
						switch($sch['filter']){//eq/like/gt/lt
							case 'like' :
								$fieldSearch[] = "".$field." LIKE '".$sch['value']."%'";
								break;
							case 'gt' :	
								if(is_numeric($sch['value'])){
									$fieldSearch[] = "".$field." > ". F::fmtNum($sch['value']) ."";
								}
								else{
									$fieldSearch[] = "".$field." > '". $sch['value'] ."'";
								}
								break;
							case 'lt' :
								if(is_numeric($sch['value'])){
									$fieldSearch[] = "".$field." < ". F::fmtNum($sch['value']) ."";
								}
								else{
									$fieldSearch[] = "".$field." < '". $sch['value'] ."'";
								}
								break;
							case 'gte' :	
								if(is_numeric($sch['value'])){
									$fieldSearch[] = "".$field." >= ". F::fmtNum($sch['value']) ."";
								}
								else{
									$fieldSearch[] = "".$field." >= '". $sch['value'] ."'";
								}
								break;
							case 'lte' :
								if(is_numeric($sch['value'])){
									$fieldSearch[] = "".$field." <= ". F::fmtNum($sch['value']) ."";
								}
								else{
									$fieldSearch[] = "".$field." <= '". $sch['value'] ."'";
								}
								break;					
							case 'eq' :
								$fieldSearch[] = "".$field." = '".$sch['value']."'";
								break;	
							case 'in' :
								$fieldSearch[] = "".$field." in (".$sch['value'].")";
								break;
							default :
								break;
						}						
					}
					$globalSearch[] = '('. implode(' and ', $fieldSearch) .')';
				}
			}
		}
		//$where = ($where) ? $where : ' where ';		
		$where .= implode(' and ', $globalSearch);
		$where = ($where === '') ? $where : ' and '.$where;
		//echo $where;
		return $where;
	}

	/* 
	$request post过来的参数
	$table 查询的table
	$where 额外的检索条件
	$maxRecordset 最大的记录条数,默认10000
	$maxPre 每页最大显示数量
	*/
	
	static function create ( $request, $table, $where, $maxRecordset = 10000, $maxPre = 100)
	{	
		/* 返回空记录集 */
		$empty = array(
				'draw' => 0,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],			
		);
		
		$request['draw'] = isset($request['draw']) ? $request['draw'] : 0;//绘制次数		



        $draw = !isset($request['draw']) ? 0 : $request['draw']; 
		$order = [];
		if(isset($request['order'])){
			foreach($request['order'] as $v){				
				if(isset($request['columns'][$v['column']]) && $request['columns'][$v['column']]['orderable'] != 'false'){					
					$order[$request['columns'][$v['column']]['data']] = ($v['dir'] == 'asc');
				}
			};
		}
		//print_r($request);
		$length = !isset($request['length']) ? 10 : $request['length']; 
		$start = !isset($request['start']) ? 0 : $request['start']; 
		$where = [];
		$whereBetween = [];
		if(isset($request['search'])){
			foreach($request['search'] as $k => $v){
				switch($k){
					case 'log_time'://操作时间
						if(array_key_exists('1', $v)){//如果传来了两个参数
							$minDate = strtotime($v[0]['value']);
							$maxDate = strtotime($v[1]['value']);	
							//$maxDate = ($maxDate - $minDate > 86400 * 30) ? 
						}else{//如果只传来了一个参数							
							if($v['filter'] == 'gte'){//最小值
								$minDate = strtotime($v['value']);
								$maxDate = time();//$minDate + 30 * 86400;
							}else{//最大值
								$maxDate = strtotime($v['value']);
								$minDate = 0;//$maxDate - 30 * 86400;								
							}
						}
						//echo $minDate;
						//echo $maxDate;
						$mongo = $mongo->whereBetween('log_time', $minDate, $maxDate);						
						break;
					case 'log_user'://操作者
						if($request['search']['log_userType']['value'] == '1'){//会员
							$uid = $db->getField("select u_id from t_user where u_nick='".$v['value']."'");							
							$where[$k] = [
								'value' => $uid,
								'num' => $v['num'],
							];							
						}else{					
							$where[$k] = [
								'value' => $v['value'],
								'num' => $v['num'],
							];							
						}
						break;
					default:
						$where[$k] = [
							'value' => $v['value'],
							'num' => $v['num'],
						];						
						break;
				}
			}
		}
		//print_r($where);die;
		$recordsFiltered = $mongo->where($where)->count('logs');
		$data = $mongo->limit($length)->offset($start)->orderBy($order)->where($where)->get('logs');				
		$recordsTotal = $mongo->count('logs');
		
		$fields = ' ' . $matchs[0] . ',';//所有查询字段		
		
		$columns = [];//别名映射
		if(preg_match_all('/(?<=\s)?((\`?(\w+\.)?\w+\`?)|(\w*\(.+?\)))(\s+as)?\s+\`?(\w+)\`?(?=[\s]*\,)/i', $fields, $matchFields)){//匹配出所有别名
			//print_r($matchFields[0]);
			foreach($matchFields[0] as $k => $v){
				$columns[$matchFields[6][$k]] = $matchFields[1][$k];
			}
		}
		
 		$limit = self::limit( $request, $columns, $maxRecordset, $maxPre);
		$order = self::order( $request, $columns );
		$where = self::filter( $request, $columns );

		// 表中总记录数量
		$tsql = preg_replace($reg, ' count(1) ', $sql, 1);//str_replace('###', 'count(1)', $sql);		
		$recordsTotal = self::$db->getField($tsql);		
			
		$sql = $sql." $where";		
		
		// 取得符合条件的记录集总条数
		$tsql = preg_replace($reg, ' count(1) ', $sql, 1);		
		$recordsFiltered = self::$db->getField($tsql);		
		
		
		$allSql = $sql." $order $limit";		
		//echo $sql;die;
		
		$data = self::$db->getAll($allSql);
		//print_r($data);die;
		
		/*
		 * 输出
		 */
		$result = array(
			"draw"            => intval( $request['draw'] ),
			"recordsTotal"    => intval( $recordsTotal ),
			"recordsFiltered" => intval( $recordsFiltered ),
			"data"            => $data
		);
		return $result;
		//print_r($result);
	}	
}

?>
