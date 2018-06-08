<?php

class interaction extends member {
	function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $id = $this->options['id'] + 0;
        $data = [
            'code' => 50102,
            'classId' => $id
        ];
        $this->setHeadTag('title', '我的提问-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $userId = $this->db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"');
        $replyList = array();
        if($userId){
            $sql = 'select tsi_id as id,tsi_title as title,tsi_classId as classId,tsi_courseId as courseId,tsi_content as content,tsi_createTime as createTime from tang_teacher_student_interaction where tsi_classId="' . $id . '" and tsi_userId="' . $userId . '" and tsi_pid=0';
            $replyList = $this->db->getAll($sql);
            foreach($replyList as $key=>&$val){
                $replySql = 'select tsi_id as id,tsi_userId,tsi_teacherId,tsi_content as content,tsi_createTime as createTime from tang_teacher_student_interaction where tsi_pid="' . $val['id'] . '"';
                $list = $this->db->getAll($replySql);
//                $val['list'] = $list;
                $val['listString'] = '';
                if($list){
                    foreach($list as $k=>$v){
                        if($v['tsi_userId'] == 0){
                            $fieldName = 'tsi_teacherId';
                        }else {
                            $fieldName = 'tsi_userId';
                        }
                        $user = $this->db->getRow('select id,username from tang_ucenter_member where id="' . $v[$fieldName] . '"');
                        $username = $user['id'] == $userId ? '我' : $user['username'];
                        $val['listString'] .=  '<label class="control-label col-md-1"></label>
                                                <div class="col-md-11">
                                                    <div class="row">
                                                        <label class="control-label col-md-2">' . $username . '：</label>
                                                        <div class="col-md-6">
                                                            <label class="control-label">' . $v['content'] . '</label>
                                                        </div>
                                                        <label class="control-label col-md-3">' . $v['createTime'] . '</label>
                                                    </div>
                                                </div>';
                    }
                }
            }
        }

//        dump($replyList);die;

        $this->setLoopData('replyList', $replyList);
        $this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
    }

}
