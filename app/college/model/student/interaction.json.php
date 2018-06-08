<?php

class interaction_json extends member {
	function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $classId  = $this->options['classId'] + 0;
        $courseId = $this->options['courseId'] + 0;
        $pid      = isset($this->options['pid']) ? $this->options['pid'] + 0 : 0;
        $userType = isset($this->options['userType']) ? $this->options['userType'] + 0 : 0;
        $content  = $this->options['reply'];

        if(!$classId || !$courseId || !$content){
            die($this->show(message::getJsonMsgStruct('1002', '参数非法')));
        }

        if(!$_SESSION || !$_SESSION['userID']){
            die($this->show(message::getJsonMsgStruct('1002', '请登录')));
        }

        $userId = $this->db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"');
        !$userId && die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));

        $data = array(
            'userId' => $userId,
            'classId'=> $classId,
            'courseId' => $courseId,
            'content'  => $content,
            'userType' => $userType,
            'interationParentId' => $pid
        );

        $result = apis::request('college/api/addInteration.json', $data, true);

        if($result['code'] != '1001'){
            die($this->show(message::getJsonMsgStruct('1002', $result['data'])));
        }

        $this->show(message::getJsonMsgStruct('1001', $result['data']));

    }

}
