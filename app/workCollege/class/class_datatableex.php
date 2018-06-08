<?php

/* ClassName: DataTables
 * Memo:DataTables class
 * Datatable数据读取
 * */

class accDataTable extends DataTables {

    static function create($request, $sql, $columns) {
        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::filter($request, $columns);

        // 表中总记录数量
//        $tsql = str_replace('###', 'count(1)', $sql);
//        $recordsTotal = self::$db->getField($tsql);

        $recordsTotal = self::$db->getField('select count(1) as num from t_account');
        
        $fields = self::join($columns);
        $sql = $sql . " $where";

        $allSql = str_replace('###', $fields, $sql . " $order $limit");
        $data = self::$db->getAll($allSql);
        //print_r($data);
        // 取得记录集总条数
//        $tsql = str_replace('###', 'count(1)', $sql);
//        $recordsFiltered = self::$db->getField($tsql);
        $tsql = 'select count(*) from t_user u where 1'. $where;
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
