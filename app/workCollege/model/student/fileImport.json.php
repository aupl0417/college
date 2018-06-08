<?php
/*=============================================================================
#     FileName: fileImport.json.php
#         Desc: 文件导入学员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-12 10:44:04
#      History:
#      Paramer: 
=============================================================================*/

class fileImport_json extends worker {
    private $classId;
    function __construct($options) {
        parent::__construct($options, [50020102]);
    }

    function run() {
        $options = $this->options;

        if (empty($options['classId'])) {
            die($this->show(message::getJsonMsgStruct('1002', '选择班级')));
        }

        if (empty($options['Files'])) {
            die($this->show(message::getJsonMsgStruct('1002', '请上传文件')));
        }

        $this->classId = $options['classId'];

        $content = file_get_contents($options['Files']['upload']['tmp_name']);
        $content = explode(",",$content);

        if (!is_array($content)) {
            die($this->show(message::getJsonMsgStruct('1002', '文件内容信息错误')));
        }

        $content = array_filter($content);

        $otherInfo = array();
        $db = new MySql();

        try{
            $db->beginTRAN();
            foreach ($content as $v) {
                if (empty($v)) {
                    continue;
                }

                $data = apis::request('/college/api/getUser.json',['mobile'=>$v],true);
                if (1002 == $data['code']) {
                    $otherInfo[] = array('msg'=>$data['data'],'mobile'=>$v); 
                    continue;
                }

                $userInfo = $data['data'];

                if ($this->importStudent($db,$userInfo)) {
                    throw new Exception('导入失败');
                    break;
                }
            }
            $db->commitTRAN();
        }catch(Exception $e){
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct('1002', '导入失败')));
        }

        die($this->show(message::getJsonMsgStruct('1001', $otherInfo)));
    }

    private function importStudent($db,$userInfo){
        $userInfo['uid'] = $db->getField("SELECT id FROM tang_ucenter_member WHERE userId='{$userInfo['id']}'");

        $addOrderRes = $this->addOrder($db,$userInfo); 

        if (!$addOrderRes) {
            return false;
        }

        $insertClassRes = $this->insertToClass($db,$userInfo);

        if (!$addOrderRes) {
            return false;
        }
    }

    private function insertToClass($db,$userInfo){
        $studentInfo = array(
            'cs_classId' => $this->classId,
            'cs_studentId' => $userInfo['uid'],
            'cs_createTime' => date("Y-m-d H:i:s")
        );

        if ($db->count('tang_class_student',"cs_studentId='{$userInfo['uid']}' AND cs_classId={$this->classId}")) {
            return true;
        }

        $res = $db->insert('tang_class_student', $studentInfo);
        return $res == 1;
    }

    private function addOrder($db,$userInfo){
        $now = F::mytime();
        //添加报名订单
        $data = array(
            'tse_id'           => F::getTimeMarkID(),
            'tse_userId'       => $userInfo['uid'],
            'tse_userTrueName' => $userInfo['name'],
            'tse_certNum'      => $userInfo['certNum'],
            'tse_classId'      => $this->classId,
            'tse_fee'          => 0,
            'tse_status'       => 2,
            'tse_state'        => 1,
            'tse_payFee'       => 0,
            'tse_eTime'        => $now,
            'tse_checkInTime'  => $now,
            'tse_createTime'   => $now,
        );

        if ($db->count('tang_student_enroll',"tse_userId='{$userInfo['uid']}' AND tse_classId={$this->classId}")) {
            return true;
        }

        $res = $db->insert('tang_student_enroll',$data);

        return $res == 1;
    }
}
