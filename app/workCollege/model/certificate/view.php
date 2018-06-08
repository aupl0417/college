<?php

class view extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040701]);
    }
	
    function run() {
 
		if(F::isEmpty($this->options['id'])){
 
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$id   = $this->options['id'] + 0;
		$data = array(
		    'code'          => 50040701,
			'tempId'		=> 'temp_'.F::getGID(),
		    'tce_id'        => $id
		);
		
        $sql = "select tce_id,tce_name,username,tce_certType,tce_condition,tce_userId,tce_userType,tce_url from tang_certificate 
               LEFT JOIN tang_ucenter_member on id=tce_userId 
               where tce_id='" . $id . "'";
        
        $db  = new MySql();
        $result = $db->getRow($sql);
        
        $condition = $db->getAll('select at_key as id,at_value as name from tang_attrib where at_type=8');
        $certType  = $db->getAll('select at_key as id,at_value as name from tang_attrib where at_type=7');
        $userType = array(array('id'=>0, 'name'=>'学员'), array('id'=>1, 'name'=>'讲师'));
        $result['tce_url'] = TFS_APIURL . '/' . $result['tce_url'];
        
        foreach ($condition as $key=>$val) {
            if($val['id'] == $result['tce_condition']){
                $data['condition'] .= "<option value='" . $val['id'] . "' selected='selected'>" . $val['name'] . "</option>";
            }else {
                $data['condition'] .= "<option value='" . $val['id'] . "'>" . $val['name'] . "</option>";
            }
        }
        
        foreach ($certType as $key=>$val) {
            if($val['id'] == $result['tce_certType']){
                $data['certType'] .= "<option value='" . $val['id'] . "' selected='selected'>" . $val['name'] . "</option>";
            }else {
                $data['certType'] .= "<option value='" . $val['id'] . "'>" . $val['name'] . "</option>";
            }
        }
        
        foreach ($userType as $key=>$val) {
            if($val['id'] == $result['tce_userType']){
                $data['userType'] .= "<option value='" . $val['id'] . "' selected='selected'>" . $val['name'] . "</option>";
            }else {
                $data['userType'] .= "<option value='" . $val['id'] . "'>" . $val['name'] . "</option>";
            }
        }
        
        $data = array_merge($data, $result);
        
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
