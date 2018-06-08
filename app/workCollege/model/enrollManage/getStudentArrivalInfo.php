<?php
/*=============================================================================
#     FileName: getStudentArrivalInfo.php
#         Desc: 获取报到学生信息
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-15 21:38:00
#      History:
=============================================================================*/
class getStudentArrivalInfo extends worker{
    public function run(){
        $options  = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct('1002','学员ID错误')));
        }

        $db   = new MySql();
        $sql = "SELECT mobile,type,level,auth,trueName,username,certNum FROM tang_ucenter_member WHERE id='{$options['id']}'";

        $info = $db->getRow($sql);

        //$param['mobile'] = $db->getField();
        //$info = apis::request('/college/api/getUser.json',$param,true);

        if (empty($info)) {
            die('没有找到该学员信息');
        }

        $info['type'] = $info['type'] == 0 ? '个人用户' : '企业用户';
        $level = array(1=>'消费商会员',3=>'创客会员','创投会员');
        $info['level'] = $level[$info['level']];
        $info['auth'] = substr($info['auth'],2,1) == 1 ? '已实名认证' : '未实名认证';
        $info['clID'] = $options['clID'];

        $sql = "SELECT tse_id,tse_team FROM tang_student_enroll WHERE tse_classId='{$options['clID']}' AND tse_userId='{$options['id']}' AND tse_state=1 AND tse_status=1";

        $tseInfo = $db->getRow($sql);
        $info['id'] = $tseInfo['tse_id'];

        $classTeamNum = $db->getField("SELECT cl_teamNum FROM tang_class WHERE cl_id='{$options['clID']}'");
        if (!empty($classTeamNum)) {
            $info['teamList'] = range(1,$classTeamNum);
            if (empty($tseInfo['tse_team'])) {
                $teamInfo = apis::request('/college/api/arrangeTeam.json',array('classId'=>$options['clID']),true);
                $info['team'] = $teamInfo['data']['team'];
            }else{
                $info['team'] = $tseInfo['tse_team']; 
            }
        }

        $data = array(
            'jsData' => json_encode($info),
            'tempId' => 'temp_' . F::getGID(),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
