<?php
/*=============================================================================
#     FileName: teamArrivalInfo.php
#         Desc: 组员报到情况
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-08 16:12:59
#      History:
#      Paramer:
=============================================================================*/
class teamArrivalInfo extends worker {
    function __construct($options) {
        parent::__construct($options, [50010301]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['clID']) || empty($options['clID'])) {
            die('<span class="font-red-thunderbird"><b>班级ID错误，请检查后重试</b></span>');
        }

        $db = new MySql();
        $clID = intval($options['clID']);
        $sql = "SELECT COUNT(1) total,tse_team,0 arrivalNum FROM tang_student_enroll WHERE tse_classId='$clID' GROUP BY tse_team";

        $teamInfo = $db->getAll($sql);

        $sql = "SELECT COUNT(tse_team) total,tse_team FROM tang_class_student cs LEFT JOIN tang_student_enroll tse ON cs.cs_classId=tse.tse_classId AND cs.cs_studentId=tse.tse_userId
            WHERE cs_classId='{$options['clID']}' AND tse.tse_state=1 AND tse.tse_status=2 GROUP BY tse.tse_team";
        $arrivalInfo = $db->getAll($sql);

        if (!empty($arrivalInfo)) {
            $arrivalInfo = array_column($arrivalInfo,'total','tse_team');
            foreach ($teamInfo as &$v) {
                $v['arrivalNum'] = $arrivalInfo[$v['tse_team']];
            }
        }

        $data = array(
            'tempId' => F::getGID(),
            'jsData' => json_encode(['teamArrivalInfo'=>$teamInfo]),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
