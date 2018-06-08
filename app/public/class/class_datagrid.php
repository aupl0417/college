<?php

/* ClassName: DataGrid
 * Memo:DataGrid class
 * Datatable改进
 * */

class DataGrid
{
	static $db = null;

    public function __construct() {
        self::$db = new MySql();
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
							else if(F::verifyDateTime($search['value'], 'Y-m-d')){
								$globalSearch[] = "".$field." <= '". $search['value'] ." 23:59:59'";
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
								else if(F::verifyDateTime($sch['value'], 'Y-m-d')){
									$fieldSearch[] = "".$field." <= '". $sch['value'] ." 23:59:59'";
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
	$sql 查询的sql
	$maxRecordset 最大的记录条数,默认10000
	$maxPre 每页最大显示数量
	*/
	/* sql语句示例
	 * SELECT ca_id, ca_from, ca_fromFlag, ca_to, ca_toFlag,ca_money, ca_businessId, ca_memo, (ca_from='94165f3b2e1b61dcd7948035ec657f93') `type`, IF((ca_from='94165f3b2e1b61dcd7948035ec657f93'), ca_fromFlag = '1' , 0) as type1, SUBSTR(ca_id, 1, 8) as `date` FROM `t_account_cash_tran` WHERE ((ca_from = '94165f3b2e1b61dcd7948035ec657f93' AND ca_fromFlag = '1') OR (ca_to = '94165f3b2e1b61dcd7948035ec657f93' AND ca_toFlag = '1')) 
	 * 如果在sql语句中有别名,那么就可以在table中直接使用别名读取/查询数据 
	 * (ca_from='94165f3b2e1b61dcd7948035ec657f93') `type`: 调用数据 - <th data-dt="type">;搜索的表单中 - <element name="type" />;
	 * IF((ca_from='94165f3b2e1b61dcd7948035ec657f93'), ca_fromFlag = '1' , 0) as type1: 调用数据 - <th data-dt="type1">;搜索的表单中 - <element name="type1" />;
	 * SUBSTR(ca_id, 1, 8) as `date`: 调用数据 - <th data-dt="date">;搜索的表单中 - <element name="date" />;
	 * 如果使用*或者原字段名称查询,那么必须使用原字段名称读取/查询数据
	 * ca_from:  用数据 - <th data-dt="ca_from">;搜索的表单中 - <element name="ca_from" />;
	*/
	static function create ( $request, $sql, $maxRecordset = 10000, $maxPre = 100)
	{	
		/* 返回空记录集 */
		$empty = array(
				'draw' => 0,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],			
		);
		$sql = preg_replace('/\n/', ' ', $sql);
		
		$reg = "/(?<=select)(.+?)(?=[\s]+from[\s\(]{1})/i";
		if(!preg_match($reg, $sql, $matchs)){//sql查询的字段必须包括在{}中
			return $empty;
			exit;
		};
		
		$request['draw'] = isset($request['draw']) ? $request['draw'] : 0;//绘制次数		
		
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
