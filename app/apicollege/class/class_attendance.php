<?php
/*=============================================================================
#     FileName: class_attendance.php
#         Desc: 统计所有员工昨天的考勤情况
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-26 16:10:50
#      History:
#      Paramer: 
=============================================================================*/
declare(ticks = 1);

class attendance {
    public static function attendanceReport() {
        $db  = new MySql();

        $sql = 'SELECT e_id,e_name,e_departmentID,IF(e_attendanceConfigID>0,e_attendanceConfigID,dm_attendanceConfigID) attendanceConfigID FROM t_employee
            e LEFT JOIN t_organization o ON o.dm_id=e.e_departmentID  
            WHERE e_state=1';

        $employeeList = $db->getAll($sql);

        if (empty($employeeList)) {
            //echo '没有员工信息';
            return false;
        }

        $yesterday = strtotime('-1 day');
        $beginTime = date('Y-m-d 00:00:00',$yesterday);
        $endTime   = date('Y-m-d 23:59:59',$yesterday);

        //所有有效班次
        //$sql = "SELECT * FROM t_attendance_classShift WHERE as_status=1";
        //$classShiftList = $db->getAll($sql);

        //if (empty($classShiftList)) {
        //    //echo '没有员工信息';
        //    return false;
        //}

        //$classShiftIdList = array_column($classShiftList,'as_id');
        //$classShiftList   = array_combine($classShiftIdList,$classShiftList);

        //所有有效考勤规则
        $sql = "SELECT * FROM t_attendance_config WHERE ac_status=1";
        $attendanceConfigList = $db->getAll($sql);

        if (empty($attendanceConfigList)) {
            //echo '没有考勤规则';
            return false;
        }

        $attendanceConfigIdList = array_column($attendanceConfigList,'ac_id');
        $attendanceConfigList   = array_combine($attendanceConfigIdList,$attendanceConfigList);

        $typeList   = array(1,2);
        $statusList = array_combine($typeList,array(3,4));

        $insertData = array();

        foreach ($employeeList as $v) {
            //上班时间
            $sql = "SELECT as_id,as_attendanceDays FROM t_attendance_classShift WHERE as_dmID={$v['e_departmentID']} AND as_beginTime<='".date('Y-m-d')."'";
            $classShiftInfo = $db->getRow($sql); 

            if (empty($classShiftInfo)) {
                continue;
            }

            //是否是上班时间
            $workDays  = explode(',',$classShiftInfo['as_attendanceDays']);
            $isWorkDay = self::isWorkDay($db,$beginTime,$workDays);

            if (!$isWorkDay) {
                continue;
            }

            //是否设置了考勤规则
            if (!isset($attendanceConfigList[$v['attendanceConfigID']])) {
                continue;
            }

            $address = $attendanceConfigList[$v['attendanceConfigID']]['ac_attendanceAddress'];

            //暂时没考虑加班打卡
            $records = array();
            $where   = " WHERE al_employeeID='{$v['e_id']}' AND al_createTime BETWEEN '$beginTime' AND '$endTime' AND al_type=%d";
            $sql     = "SELECT al_type,al_status FROM t_attendance_logs $where GROUP BY al_type,al_createTime";

            foreach ($typeList as $tl) {
                $records = $db->getRow(sprintf($sql,$tl));
                if (empty($records)) {
                    $insertData[] = self::logsInsertData($beginTime,$classShiftInfo['as_id'],$v,$typeList,$statusList,$tl,$address);
                }
            }
        }

        if (!empty($insertData)) {
            $logs        = array_column($insertData,'logs');
            $specialLogs = array_column($insertData,'specialLogs');

            try{
                $db->beginTRAN();
                $logsKeys        = array_keys(pos($logs));
                $specialLogsKeys = array_keys(pos($specialLogs));

                if (0 > $db->inserts('t_attendance_logs',$logsKeys,$logs)) {
                    throw new Exception(-1);
                }

                if (0 > $db->inserts('t_attendance_specialLogs',$specialLogsKeys,$specialLogs)) {
                    throw new Exception(-2);
                }

                $db->commitTRAN();
                return true;
            }catch(Exception $e){
                $db->rollBackTRAN();
                return $e->getMessage();
            }
        }

        return true;
    }

    /* --------------------------------------------------------------------------*/
    /**
     * @将异常记录写入考勤记录表  
     * @Param $beginTime     考勤时间
     * @Param $classShiftID  排班ID
     * @Param $employeeInfo  员工信息
     * @Param $typeList      考勤类型 1-签到 2-签退
     * @Param $statusList
     *
     * @Returns  array
     */
    /* ----------------------------------------------------------------------------*/
    private function logsInsertData($beginTime,$classShiftID,$employeeInfo,$typeList,$statusList,$type,$address){
        //没有考勤记录
        return array(
            'logs'                => array(
                'al_employeeID'   => $employeeInfo['e_id'],
                'al_employeeName' => $employeeInfo['e_name'],
                'al_dmID'         => $employeeInfo['e_departmentID'],
                'al_createTime'   => $beginTime,
                'al_type'         => $type,
                'al_address'      => $address,
                'al_classShiftID' => $classShiftID,
                'al_status'       => $statusList[$type],
            ),
            'specialLogs'          => array(
                'asl_employeeID'   => $employeeInfo['e_id'],
                'asl_employeeName' => $employeeInfo['e_name'],
                'asl_dmID'         => $employeeInfo['e_departmentID'],
                'asl_address'      => $address,
                'asl_classShiftID' => $classShiftID,
                'asl_specialType'  => $statusList[$type],
                'asl_createTime'   => $beginTime,
            ),
        );
    }

    //是否是上班时间
    private function isWorkDay($db,$beginTime,$workDays){
        $holidy = $db->getRow("SELECT ah_beginTime,ah_endTime FROM t_attendance_holiday_config WHERE ah_beginTime>='$beginTime'");
        if ($holidy) {
            if ($beginTime >= $holidy['ah_beginTime'] && $beginTime <= $holidy['ah_endTime']) {
                return false;
            }
        }

        if (!in_array(date('w',strtotime($beginTime)),$workDays)) {
            return false;
        }

        return true;
    }


    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  统计某员工的考勤情况
        *
        * @Param $beginTime 开始时间
        * @Param $endTime   结束时间
        * @Param $employeeID    员工ID
        *
        * @Returns array|bool
     */
    /* ----------------------------------------------------------------------------*/
    public function monthReport($employeeID,$beginTime,$endTime){
        //$params = array_filter(func_get_args());
        if (!empty($beginTime) && !empty($endTime)) {
           $beginTime = date('Y-m-1 00:00:00');
           $endTime   = date('Y-m-t 23:59:59');
        }

        if (empty($employeeID)) {
            return false;
        }

        $reportSituationKeys = array('normal','late','earlyLeave','notCheckIn','notCheckOut','absenteeism','vacation');
        $retval = array_fill_keys($reportSituationKeys,0);

        $db = new MySql();
        $sql = "SELECT al_status,COUNT(1) total FROM t_attendance_logs WHERE al_employeeID='$employeeID' AND al_createTime BETWEEN '$beginTime' AND '$endTime'
             GROUP BY al_status";
        $reportList = $db->getAll($sql); 

        if (!empty($reportList)) {
            //$reportSituation = array_flip($reportSituation);
            foreach ($reportList as $v) {
                $retval[$reportSituationKeys[$v['al_status']]] = $v['total'];
            }
        }
        $retval['attendanceDays'] = $retval['normal'] + $retval['late'] + $retval['earlyLeave'];
        return $retval;
    }
}
