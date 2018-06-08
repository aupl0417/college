<?php
/*
 * 国家
 * jojojing
 */

class political_json extends worker {

    function run() {
		$result = F::getAttrs(18);
		echo json_encode($result);
    }
}