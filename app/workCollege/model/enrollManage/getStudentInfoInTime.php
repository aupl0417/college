<?php
/*=============================================================================
#     FileName: getStudentInfoInTime.php
#         Desc: 从缓存获取报到学生信息
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-19 17:24:46
#      History:
=============================================================================*/
class getStudentInfoInTime extends worker{
    public function run(){
        $options  = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct('1002','请选择报名班级')));
        }

        $cache = new cache();
        $key = 'checkInScan_'.$_SESSION['userID']."_{$options['id']}";

        $info = $cache->get($key);

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct('1002','没有获取到学员报到信息')));
        }

        $db               = new MySql();
        $info['tempId']   = 'temp_'.F::getGID();
        $info['clId']     = $options['id'];
        $info['teamList'] = [];

        $sql = "SELECT tse_id,tse_team FROM tang_student_enroll tse LEFT JOIN tang_ucenter_member um ON um.id=tse.tse_userId
            WHERE tse_classId='{$options['id']}' AND um.username='{$info['username']}' AND tse.tse_state=1 AND tse.tse_status=1";
        $tseInfo = $db->getRow($sql);

        $info['id'] = $tseInfo['tse_id'];

        $classTeamNum = $db->getField("SELECT cl_teamNum FROM tang_class WHERE cl_id='{$options['id']}'");
        if (!empty($classTeamNum)) {
            $info['teamList'] = range(1,$classTeamNum);
            if (empty($tseInfo['tse_team'])) {
                $teamInfo = apis::request('/college/api/arrangeTeam.json',array('classId'=>$options['id']),true);
                $info['team'] = empty($teamInfo) || 1001 != $teamInfo['code'] ? 1 : $teamInfo['data']['team'];
            }else{
                $info['team'] = $tseInfo['tse_team']; 
            }
        }

        die($this->show(message::getJsonMsgStruct('1001',$info)));
    }
}
