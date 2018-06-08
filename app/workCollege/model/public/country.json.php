<?php
/*
 * 国家
 * jojojing
 */

class country_json extends worker {

    function run() {
		$db = new MySql();
		$sql = "SELECT coun_id,coun_name FROM t_country ORDER BY coun_id=37 DESC";
		$result = $db->getAll($sql);
		$res = array_column($result,'coun_name','coun_id');
		echo json_encode($res);
    }
}
