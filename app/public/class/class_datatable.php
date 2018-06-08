<?php

/* ClassName: DataTables
 * Memo:DataTables class
 * Datatable数据读取
 * */

class DataTables {

    static $db = null;

    public function __construct() {
        self::$db = new MySql();
    }

    static function limit($request, $columns) {
        $limit = '';

        if (!isset($request['start'])) {
            $request['start'] = 0;
        }
        if (!isset($request['length'])) {
            $request['start'] = 100;
        }

        if (isset($request['start']) && $request['length'] != -1) {
            $limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
        }

        return $limit;
    }

    static function order($request, $columns) {
        $order = '';

        if (isset($request['order']) && count($request['order'])) {
            $orderBy = array();
            $dtColumns = self::pluck($columns, 'dt');

            for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
                // Convert the column index into the column data property			

                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];

                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                            'ASC' :
                            'DESC';

                    $orderBy[] = '' . $column['db'] . ' ' . $dir;
                }
            }
            if ($orderBy) {
                $order = 'ORDER BY ' . implode(', ', $orderBy);
            }
        }

        return $order;
    }

    static function pluck($a, $prop) {
        $out = array();

        for ($i = 0, $len = count($a); $i < $len; $i++) {
            $out[] = $a[$i][$prop];
        }

        return $out;
    }

    static function join($columns) {
        $fields = array();

        foreach ($columns as $col) {
            $fields[] = $col['db'] . ' as ' . $col['dt'];
        }
        return implode(',', $fields);
    }

    static function filter($request, $columns, $where = '') {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = self::pluck($columns, 'dt');

        if (isset($request['search']) && is_array($request['search'])) {
            foreach ($request['search'] as $key => $search) {
                $columnIdx = array_search($key, $dtColumns);
                if ($columnIdx) {
                    $field = $columns[$columnIdx]['db'];
                }
                if (isset($search['filter'])) {//单个查询条件
                    switch ($search['filter']) {//eq/like/gt/lt
                        case 'like' :
                            $globalSearch[] = "" . $field . " LIKE '" . $search['value'] . "%'";
                            break;
                        case 'gt' :
                            if (is_numeric($search['value'])) {
                                $globalSearch[] = "" . $field . " > " . F::fmtNum($search['value']) . "";
                            } else {
                                $globalSearch[] = "" . $field . " > '" . $search['value'] . "'";
                            }
                            break;
                        case 'lt' :
                            if (is_numeric($search['value'])) {
                                $globalSearch[] = "" . $field . " < " . F::fmtNum($search['value']) . "";
                            } else {
                                $globalSearch[] = "" . $field . " < '" . $search['value'] . "'";
                            }
                            break;
                        case 'gte' :
                            if (is_numeric($search['value'])) {
                                $globalSearch[] = "" . $field . " >= " . F::fmtNum($search['value']) . "";
                            } else {
                                $globalSearch[] = "" . $field . " >= '" . $search['value'] . "'";
                            }
                            break;
                        case 'lte' :
//                             echo F::verifyDateTime($sch['value'], 'Y-m-d') - 0;
							if (is_numeric($search['value'])) {
                                $globalSearch[] = "" . $field . " <= " . F::fmtNum($search['value']) . "";
                            }
							else if(F::verifyDateTime($search['value'], 'Y-m-d')){
								$globalSearch[] = "".$field." <= '". $search['value'] ." 23:59:59'";
							}
							else {
                                $globalSearch[] = "" . $field . " <= '" . $search['value'] . "'";
                            }
                            break;
                        case 'eq' :
                            $globalSearch[] = "" . $field . " = '" . $search['value'] . "'";
                            break;
                        default :
                            break;
                    }
                } else {//多个查询条件
                    $fieldSearch = array();
                    foreach ($search as $k => $sch) {
                        switch ($sch['filter']) {//eq/like/gt/lt
                            case 'like' :
                                $fieldSearch[] = "" . $field . " LIKE '" . $sch['value'] . "%'";
                                break;
                            case 'gt' :
                                if (is_numeric($sch['value'])) {
                                    $fieldSearch[] = "" . $field . " > " . F::fmtNum($sch['value']) . "";
                                } else {
                                    $fieldSearch[] = "" . $field . " > '" . $sch['value'] . "'";
                                }
                                break;
                            case 'lt' :
                                if (is_numeric($sch['value'])) {
                                    $fieldSearch[] = "" . $field . " < " . F::fmtNum($sch['value']) . "";
                                } else {
                                    $fieldSearch[] = "" . $field . " < '" . $sch['value'] . "'";
                                }
                                break;
                            case 'gte' :
                                if (is_numeric($sch['value'])) {
                                    $fieldSearch[] = "" . $field . " >= " . F::fmtNum($sch['value']) . "";
                                } else {
                                    $fieldSearch[] = "" . $field . " >= '" . $sch['value'] . "'";
                                }
                                break;
                            case 'lte' :                               
								if (is_numeric($sch['value'])) {
                                    $fieldSearch[] = "" . $field . " <= " . F::fmtNum($sch['value']) . "";
                                }
								else if(F::verifyDateTime($sch['value'], 'Y-m-d')){
									$fieldSearch[] = "".$field." <= '". $sch['value'] ." 23:59:59'";
								}								
								else {
                                    $fieldSearch[] = "" . $field . " <= '" . $sch['value'] . "'";
                                }
                                break;
                            case 'eq' :
                                $fieldSearch[] = "" . $field . " = '" . $sch['value'] . "'";
                                break;
                            default :
                                break;
                        }
                    }
                    $globalSearch[] = '(' . implode(' and ', $fieldSearch) . ')';
                }
            }
        }
        //$where = ($where) ? $where : ' where ';		
        $where .= implode(' and ', $globalSearch);
        $where = ($where === '') ? $where : ' and ' . $where;
        //echo $where;
        return $where;
    }

    static function data_output($columns, $data) {
        $out = array();

        for ($i = 0, $ien = count($data); $i < $ien; $i++) {
            $row = array();

            for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
                $column = $columns[$j];
                /* 					$pos = strpos($columns[$j]['db'], '.');
                  if($pos > 0){
                  $d_key = substr($columns[$j]['db'], $pos + 1);
                  }else{
                  $d_key = $columns[$j]['db'];
                  } */
                $d_key = $columns[$j]['dt'];
                //print_r($data[$i]);
                // Is there a formatter?
                if (isset($column['formatter'])) {
                    $row[$column['dt']] = $column['formatter']($data[$i][$d_key], $data[$i]);
                } else {
                    //echo substr($columns[$j]['db'], $pos + 1);die;$columns[$j]['db']
                    $row[$column['dt']] = $data[$i][$d_key];
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    static function create($request, $sql, $columns) {
        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::filter($request, $columns);

        // 表中总记录数量
        $tsql = str_replace('###', 'count(1)', $sql);
        $recordsTotal = self::$db->getField($tsql);

        $fields = self::join($columns);
        /* 		$fields = array_unique(array_column($columns, 'db'));
          print_r($fields);die;
          $fields = implode(',', $fields); */
        //
        //$sql = "SELECT ".$fields." FROM $table $where $order $limit";
        $sql = $sql . " $where";

        $allSql = str_replace('###', $fields, $sql . " $order $limit");
       // echo $allSql;die;
        $data = self::$db->getAll($allSql);
        //print_r($data);
        // 取得记录集总条数
        $tsql = str_replace('###', 'count(1)', $sql);

        $recordsFiltered = self::$db->getField($tsql);

        /*
         * 输出
         */
        $result = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => self::data_output($columns, $data)
        );
        return $result;
        //print_r($result);
    }

}

?>
