<?php
/*
 * 国家
 * jojojing
 */

class education_json extends worker {

    function run() {
		$result = F::getAttrs(22);
		echo json_encode($result);
    }
}
