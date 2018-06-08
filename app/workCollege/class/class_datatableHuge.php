<?php

/* ClassName: DataTables
 * Memo:DataTables class
 * Datatable数据读取
 * 数据量大的表,返回总记录数为10000,总页数为100,不去计算
 * */

class datatableHuge extends DataTables {
	static public $maxRecords = 10000;//最大记录数
	static public $maxPages = 100;//最大页数
	static public $useCache = true;//使用缓存,用于保存检索条件筛选出来的总记录数
	static function setMaxRecords($num = 10000){
		self::$maxRecords = $num;
	}
	static function setMaxPages($num = 10000){
		self::$maxPages = $num;
	}
	static function setUseCache($tof = true){
		self::$useCache = $tof;
	}
	
    static function create($request, $sql, $columns) {
        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::filter($request, $columns);

        // 表中总记录数量

        $recordsTotal = self::$maxRecords;
        
        $fields = self::join($columns);
        $sql = $sql . " $where";

        $allSql = str_replace('###', $fields, $sql . " $order $limit");
		//echo $allSql;die;
        $data = self::$db->getAll($allSql);
      
		$tsql = str_replace('###', 'count(1)', $sql);
		if(self::$useCache){//缓存筛选出来的记录数
			$cache = new cache();
			$key = md5(base64_encode($tsql));//将sql语句编码后,再md5 用于key//防止中文md5出现bug
			$num = $cache->get($key);
			if($num){//如果相同的检索条件有值
				$recordsFiltered = $num;
			}else{
				$recordsFiltered = self::$db->getField($tsql);
				$recordsFiltered = $recordsFiltered > self::$maxRecords ? self::$maxRecords : $recordsFiltered;
				$cache->set($key, $recordsFiltered, 3600);
			}
		}
        
        

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
