<?php

class collect_json extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        (!isset($this->options['classId']) || empty($this->options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '班级ID不能为空')));
        
        $isCollect = isset($this->data['col']) ? $this->data['col'] + 0 : 1;
        $classId  = $this->options['classId'] + 0;
        
        $userId = $this->db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"');
        $count = $this->db->getField('select count(tcc_id) from tang_class_collection where tcc_classId="' . $classId .'" and tcc_userId="' . $userId .'"');
        $return = array('status' => 0, 'imgUrl' => '{_TEMP_SHARE_}/themes/default/images/collection_a.png');
        if(!$count){
            $data = array(
                'tcc_classId'  => $classId,
                'tcc_userId'   => $userId,
                'tcc_deviceId' => '',
                'tcc_createTime' => date('Y-m-d H:i:s')
            );
            
            $res = $this->db->insert('tang_class_collection', $data);
            !$res && die($this->show(message::getJsonMsgStruct('1002', $return)));
            $return = array('status' => 1, 'imgUrl' => '{_TEMP_SHARE_}/themes/default/images/collection_b.png');
        }else {
            $res = $this->db->delete('tang_class_collection', 'tcc_classId="' . $classId .'" and tcc_userId="' . $userId .'"');
            !$res && die($this->show(message::getJsonMsgStruct('1002')));
            $return = array('status' => 1, 'imgUrl' => '{_TEMP_SHARE_}/themes/default/images/collection_a.png');
        }
        
        $this->show(message::getJsonMsgStruct('1001', $return));
    }
}
