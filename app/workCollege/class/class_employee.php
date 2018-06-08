<?php
/*=============================================================================
#     FileName: class_employee.php
#         Desc: 雇员类 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-07-14 09:30:42
#      History:
#      Paramer: 
=============================================================================*/
class employee extends worker{
    private $employeeID; //员工工号
    private $archiveID;  //人事档案编号
    private $error;
    private $mongodb;
    private $db;

    function __construct($employeeID='', $db=null){
       $this->db      = is_null($db) ? new MySql() : $db;
       $this->mongodb = new mgdb();
       $this->employeeID = empty($employeeID) ? '' : trim($employeeID);
    }

    public function selectKey($keyName, $value){
        if (empty($employeeInfo) || empty($value)) {
            $this->getError('参数错误');
            return false;
        }
        $this->$$keyName = $value;
        return $this;
    }


    public function archiveExist($employeeID,$archiveInfoType){
        $employeeInfo = $this->db->getRow("SELECT ar_employeeID,ar_employeeName FROM t_employee_archive WHERE ar_archiveID=".$this->$archiveID);
        return $employeeInfo;
    }

    /* --------------------------------------------------------------------------*/
    /**
     * @获取员工档案ID  
     * @Param $employee 员工ID
     * @int   
     */
    /* ----------------------------------------------------------------------------*/
    private function getArchiveID($employee){
        
    }

    //获取员工档案基本信息
    public function getEmployeeBaseInfo($fields='*'){
        $employeeInfo = $this->db->getRow("SELECT $fields FROM t_employee_archive WHERE ar_employeeID='".$this->employeeID."'");
        return $employeeInfo;
    }

    protected function getEmployeeArchiveFiles(){

    }

    protected function getEmployeeArchvieCompany(){
        
    }

    public function saveEmployeeCompany(){
        
    }

    //工龄
    public function getWorkAge(){
        if (empty($this->employeeID)) {
            return false;
        }

        $joinTime = $this->getEmployeeBaseInfo('ar_joinTime',$this->employeeID);
        $joinTime = new DateTime($joinTime['ar_joinTime']);
        $today    = new DateTime(date('Y-m-d'));
        $interval = $joinTime->diff($today);
        $workAge  = $interval->format('%y');
        return $workAge;
    }

    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  创建员工ID
        * @Param $dmCode  部门code string
        * @Returns  string|bool
     */
    /* ----------------------------------------------------------------------------*/
    public function createEmployeeID($dmCode){
        if (empty($dmCode)) {
            $this->error = '-21';
            return false;
        }

        $preOrgCode = substr($dmCode,0,4);
        $sql = "SELECT dm_id,dm_employeeIDStart,dm_employeeIDEnd FROM t_organization WHERE dm_code='$preOrgCode'";
        $employeeIDRule = $this->db->getRow($sql);

        if (empty($employeeIDRule['dm_employeeIDStart']) || empty($employeeIDRule['dm_employeeIDEnd'])) {
            $this->error = '部门员工工号的起始没设置';
            return false;
        }

        $startID = $employeeIDRule['dm_employeeIDStart'];
        $endID   = $employeeIDRule['dm_employeeIDEnd'];
        $lastID  = $startID;

        $sql = "SELECT e_id FROM t_employee e LEFT JOIN t_organization o ON e.e_departMentID=o.dm_id
            WHERE LEFT(dm_code,4)='$preOrgCode' ORDER BY e_id DESC";
        $lastEmployeeID = $this->db->getField($sql);

        if ($lastEmployeeID) {
            preg_match('/\d+/',$lastEmployeeID,$lastID);
            $lastID = intval(current($lastID));
            if ($lastID == $endID) {
                return false;
            }
            $lastID++; 
        }

        //分配员工编号
        $employeeID = 'dttx'.str_pad("$lastID",5,0,STR_PAD_LEFT);

        return $employeeID;
    }

    //获取员工系统账号信息
    public function getEmployeeInfo($fields='*'){
        $res = $this->db->getRow("select $fields from t_employee where e_id = '".$this->employeeID."'");

        if (empty($res)) {
            $this->error = '获取员工系统账号信息失败';
            return false;
        }

        return $res;
    }

    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  记录员工的考勤信息（未签到，未签退）
        *
        * @Param $beginTime
        * @Param $endTime
        *
        * @Returns   
     */
    /* ----------------------------------------------------------------------------*/
    public function recordAttendanceLogs($beginTime,$endTime){
        //部门
        $dmID = $this->db->getField("SELECT e_departmentID FROM t_employee WHERE e_id='{$this->employeeID}'");

        //上班时间
        $classShiftInfo = $this->db->getRow("SELECT as_id,as_attendanceDays FROM t_attendance_classShift WHERE as_dmID='$dmID'"); 

        if (empty($classShiftInfo)) {
            return apis::apiCallback('1002', '获取班次信息失败错误');
        }

        $where .= " AND al_employeeID='{$options['employeeID']}' AND al_createTime BETWEEN '$beginTime' AND '$endTime'";

        if (isset($options['employeeName']) || !empty($options['employeeName'])) {
            $where .= " AND al_employeeName LIKE '{$options['employeeName']}%'";
        }

        $sql     = "SELECT * FROM t_attendance_logs $where";
        $records = $this->db->getAll($sql);

    }

    //获取错误信息
    private function getError($message){
        $this->error = $message;
        return $this->error;
    }
}

?>
