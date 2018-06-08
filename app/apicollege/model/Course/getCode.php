<?php

class getCode extends baseApi{
    private $db;
    
    function __construct($options) {
        parent::__construct($options);
		$this->db = new MySql();
    }
	
	function run(){
		$periodId = $this->data['periodId'] + 0;
		$size     = (isset($this->data['size']) && !empty($this->data['size'])) ? $this->data['size'] : 7;
		!is_numeric($size) && $this->apiReturn(1004);
		$periodId == 0 && $this->apiReturn(2001);
		
		if(!$this->db->getField('select count(cta_id) from tang_class_table where cta_id="' . $periodId . '"')){
		    $this->apiReturn(2002);
		}
		
	    $data = 'tangcollege_' . $periodId;
	    $code = new MyQRCode();
	    echo $result = $code->getOutHtml($data, '', $size);
	}
}
