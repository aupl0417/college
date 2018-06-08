<?php

class getInfoByCode extends guest {

    private $db;
    function __construct($options) {        		
        parent::__construct($options, []);	
        $this->db = new MySql();		
    }

    function run() {	
        $options = $this->options;
        $code = isset($options['code']) ? $options['code'] : "";
        $code = trim($code);
        if($code == ""){
            echo "false";
        }else {
            $ret = "";
            $zh = iconv("UTF-8","gb2312", $code);
            for($i = 0; $i < strlen($zh); $i++){
                $s1 = substr($zh,$i,1);
                $p = ord($s1);
                if($p > 160){
                    $s2 = substr($zh,$i++,2);
                    $ret .= $this->getfirstchar($s2);
                }else{
                    $ret .= $s1;
                }
            }
            echo $ret;
        }
    }
    function getfirstchar($s0){
        $fchar = ord($s0{0});
        if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
        $s = $s0;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if($asc >= -20319 and $asc <= -20284) return "a";
        if($asc >= -20283 and $asc <= -19776) return "b";
        if($asc >= -19775 and $asc <= -19219) return "c";
        if($asc >= -19218 and $asc <= -18711) return "d";
        if($asc >= -18710 and $asc <= -18527) return "e";
        if($asc >= -18526 and $asc <= -18240) return "f";
        if($asc >= -18239 and $asc <= -17923) return "g";
        if($asc >= -17922 and $asc <= -17418) return 'h';
        if($asc >= -17922 and $asc <= -17418) return "i";
        if($asc >= -17417 and $asc <= -16475) return 'j';
        if($asc >= -16474 and $asc <= -16213) return "k";
        if($asc >= -16212 and $asc <= -15641) return "l";
        if($asc >= -15640 and $asc <= -15166) return "m";
        if($asc >= -15165 and $asc <= -14923) return "n";
        if($asc >= -14922 and $asc <= -14915) return "o";
        if($asc >= -14914 and $asc <= -14631) return "p";
        if($asc >= -14630 and $asc <= -14150) return "q";
        if($asc >= -14149 and $asc <= -14091) return "r";
        if($asc >= -14090 and $asc <= -13319) return "s";
        if($asc >= -13318 and $asc <= -12839) return "t";
        if($asc >= -12838 and $asc <= -12557) return "w";
        if($asc >= -12556 and $asc <= -11848) return "x";
        if($asc >= -11847 and $asc <= -11056) return "y";
        if($asc >= -11055 and $asc <= -10247) return "z";
        return null;
    }
}