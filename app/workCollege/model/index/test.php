<?php

class test extends guest {

	private $db;
	private $csah_table;
	private $score_table;
    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
		$this->db = new MySql();
    }

    function run() {
			
		echo date('md', time())/1000;	
			
		die;	
		$arr = array(4);
		print_r($arr);
		//echo date('Y-m-d H:i:s', strtotime('+6 month'));
		print_r(attrib::initAreaOptions('12'));
		
		die;
		$array=array('12','4','8','5','1','7','6','9','10','3');

		echo "Unsorted array is: ";
		echo "<br />";
		print_r($array);


		for($j = 0; $j < count($array); $j ++) {
			for($i = 0; $i < count($array)-1; $i ++){

				if($array[$i] > $array[$i+1]) {
					$temp = $array[$i+1];
					$array[$i+1]=$array[$i];
					$array[$i]=$temp;
					dump($j.' - '.$i);
					dump($array);
				}       
			}
		}

		echo "Sorted Array is: ";
		echo "<br />";
		print_r($array);
	}	
}
