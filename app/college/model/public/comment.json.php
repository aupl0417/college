<?php

class comment_json extends guest {

    function run() {
//		dump($this->options);
//        $this->show(message::getJsonMsgStruct('1001', $this->options));
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '班级ID非法')));
//        !$_SESSION['userID'] && die($this->show(message::getJsonMsgStruct('1002', '请先登录')));
        (!isset($this->options['content']) || empty($this->options['content'])) && die($this->show(message::getJsonMsgStruct('1002', '请输入评论内容')));
        $classId = $this->options['id'] + 0;
        $userId  = $_SESSION['userID'];
        $content = $this->options['content'];
        $level   = isset($this->options['level']) && !empty($this->options['level']) ? $this->options['level'] + 0 : 5;
        
        if(mb_strlen($content, 'utf8') > 500){
            die($this->show(message::getJsonMsgStruct('1002', '评论内容已超过500个字')));
        }
        
        $db = new MySql();
        $userInfo = $db->getRow('select id,username,avatar from tang_ucenter_member where userId="' . $userId . '"');
        if(!$userInfo){
            die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));
        }

        $count = $db->getField('select count(tc_id) from tang_teacher_comment where tc_userId="' . $userInfo['id'] . '" and tc_classId="' . $classId . '"');
        $count && die($this->show(message::getJsonMsgStruct('1002', '您已对该班级评论过了')));

        $data = array(
            'tc_classId' => $classId,
            'tc_userId'  => $userInfo['id'],
            'tc_content' => $content,
            'tc_level'   => $level,
            'tc_createTime' => date('Y-m-d H:i:s'),
        );

        $id = $db->insert('tang_teacher_comment', $data);
        if(!$id){
            die($this->show(message::getJsonMsgStruct('1002', '发表失败')));
        }

        $return = array(
            'username' => $userInfo['username'],
            'avatar'   => $userInfo['avatar'] ?: 'https://image.dttx.com/v1/tfs/T1AddTByYT1RCvBVdK.png',
            'content'  => $content,
            'createTime' => $data['tc_createTime'],
            'level'    => 5
        );

        $this->show(message::getJsonMsgStruct('1001', $return));
    }
}
