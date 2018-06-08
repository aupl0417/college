<?php

class empty_json extends guest {

    function run() {		
		//{"draw":0,"recordsTotal":0,"recordsFiltered":0,"data":[]}	
		$result = array(
			'draw' => 0,
			'recordsTotal' => 0,
			'recordsFiltered' => 0,
			'data' => [],			
		);
		echo json_encode($result);
    }

}
