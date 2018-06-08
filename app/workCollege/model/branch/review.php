<?php

class review extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);
        $this->db  = new MySql();
    }
	
    function run() {
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		} 
		$id   = $this->options['id'] + 0;
		
		$provinceList = $this->getAreaListByFkey();//省份列表
		
		$sql  = 'select br_id,br_name,br_parentId,br_areaId,br_address,br_state,a_id from tang_branch left join tang_area on br_parentId=a_code where br_id="' . $id . '"';
		$data = $this->db->getRow($sql);
		
		$provinceCode = $this->getProvinceId($data['a_id']);//当前所在的省份
		$cityList   = $this->getAreaListByFkey($provinceCode);
		$contyList  = $this->getAreaListByFkey($data['br_parentId']);
		
		$data['provinceList'] = $this->array2Option($provinceList, 'id', 'name', $provinceCode);
		$data['cityList']     = $this->array2Option($cityList, 'id', 'name', $data['br_parentId']);
		$data['contyList']    = $this->array2Option($contyList, 'id', 'name', $data['br_areaId']);
		$data['code']    = 50040401;
		$data['tempId']    = 'temp_'.F::getGID();
		
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
    
//获取省份id
    private function getProvinceId($cityId){
        $sql = 'select a_code,a_fkey from tang_area where a_id="' . $cityId . '"';
        
        $data = $this->db->getRow($sql);
        
        if($data['a_fkey'] != 0){
            return self::getProvinceId($data['a_fkey']);
        }else {
            return $data['a_code'];
        }
    }
    
    private function getAreaListByFkey($fkey = 0){
        $sql = "select a_code as id, a_name as name from tang_area where ";
        if(!$fkey){
            $sql .=  "a_fkey=0";
        }else {
            $sql .= "a_fkey=(select a_id from tang_area where a_code='" . $fkey . "')";
        }
        
        $data = $this->db->getAll($sql);
        
        return $data;
    }
    
    private function array2Option($optionList, $keys, $values, $selectId){
        $optionStr = '';
        foreach($optionList as $key=>$val){
            if(is_array($val)){
                if($selectId == $val['id']){
                    $optionStr .= '<option value="' . $val[$keys] . '" selected="selected">' . $val[$values] . '</option>';
                }else {
                    $optionStr .= '<option value="' . $val[$keys] . '">' . $val[$values] . '</option>';
                }
            }
        }
        
        return $optionStr;
    }
}
