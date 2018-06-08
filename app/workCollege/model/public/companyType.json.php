<?php
/*
 * 企业类型
 * jojojing
 */

class companyType_json extends worker {

    function run() {
		$companyTypes = F::getAttrs(4);
		echo json_encode($companyTypes);
    }
}
