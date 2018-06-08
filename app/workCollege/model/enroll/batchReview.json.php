<?php
/*
 * 批量审核
 * params $ids 报名订单id集
 *   */
class batchReview_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    function run() {
        $options = $this->options;
        
        (!isset($options['ids']) || empty($options['ids'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择报名订单')));
        

        //wyh 改写start
        $db = new MySql();
        $ids = implode("','",$options['ids']);

        $sql = "SELECT tse_id FROM tang_student_enroll WHERE tse_id IN('{$ids}') AND tse_state=0 AND tse_status=1";
        $ids = $db->getAll($sql);

        if(!count($ids)){
            die($this->show(message::getJsonMsgStruct('1002', '请选择未审核的报名订单')));
        }

        $ids = array_column($ids,'tse_id');
        //wyh 改写end

        try{
            $db->beginTRAN();
            $data = array(
                'tse_state'  => 1,
                'tse_eId'    => $_SESSION['userID'],
                'tse_eTime'  => date('Y-m-d H:i:s')
            );

            $noticeParam = array(
                'state'  => $options['state'],
                'tseID'  => $options['id'],
            );

            $classId = $db->getField("SELECT tse_classId FROM tang_student_enroll WHERE tse_id='".pos($ids)."'");
            foreach ($ids as $v) {
                $teamInfo = apis::request('/college/api/arrangeTeam.json',array('classId'=>$classId),true);
                $data['tse_team'] = $teamInfo['data']['team'];
                $res = $db->update('tang_student_enroll', $data, "tse_id='$v'");
                if (1 != $res) {
                   throw new Exception(1002);
                }

                apis::request('/college/api/enrollReviewNotice.json',$noticeParam,true);
                apis::request('/college/api/sendEnrollCheckSms.json',array('id'=>$v),true);
            }

            $db->commitTRAN();
            die($this->show(message::getJsonMsgStruct('1001', '操作成功')));    
        }catch(Exception $e){
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct('1002', '操作失败')));
        }
    }

    function arrangeTeam($db,$classId){
        if (empty($classId)) {
            return 0;
        }

        $classTeamInfo = $db->getRow("SELECT cl_teamNum,cl_teamStudentNum FROM tang_class WHERE cl_id='{$classId}'");

        if (empty($classTeamInfo)) {
            return 0;
        }

        $sql = "SELECT COUNT(1) total,tse_team FROM tang_student_enroll
            WHERE tse_classId='{$classId}' AND tse_state=1 AND tse_status<>-1 AND tse_team<>0 GROUP BY tse_team ";
        $teamSituation = $db->getAll($sql."HAVING total<='{$classTeamInfo['cl_teamStudentNum']}'");

        if (empty($teamSituation)) {
            $teamCount = $db->getAll($sql);
            return count($teamCount) > 0 ? 0 : 1; 
        }

        $currentTeamInfo = pos($teamSituation);
        return $currentTeamInfo['tse_team'];
    }
}
