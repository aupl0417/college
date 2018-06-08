<?php

class reply extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040503]);
    }
	
    function run() {
        
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$data = array(
		    'code'          => 50040503,
			'tempId'		=> 'temp_'.F::getGID(), 
		);
		$id   = $this->options['id'] + 0;
        $sql = "select f_id as fid,f_content as content,username,f_createTime from tang_feedback 
               LEFT JOIN tang_ucenter_member on f_userId=id 
               where f_id='" . $id . "'";
        
        $db  = new MySql();
        $result = $db->getRow($sql);
        
        $replyData = $db->getRow('select f_content from tang_feedback where f_replyId="' . $result['fid'] .'"');
        
        $classList = $db->getAll('select cl_id, cl_name from tang_class');//暂时全部选择，以后根据所在分院及下属分院来获取
        $data['classList'] = '';
        foreach ($classList as $key=>$val) {
            if($val['cl_id'] == $result['cl_id']){
                $data['classList'] .= "<option value='" . $val['co_id'] . "' selected='selected'>" . $val['co_name'] . "</option>";
            }else {
                $data['classList'] .= "<option value='" . $val['co_id'] . "'>" . $val['co_name'] . "</option>";
            }
        }
        
        $result['isReply'] = count($replyData) ? '是' : '否';
        $result['replyContent'] = $replyData['f_content'];
        $data = array_merge($data, $result);
        
        $this->setLoopData('replyList', $replyData);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
