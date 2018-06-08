<?php
/*=============================================================================
#     FileName: exl.json.php
#         Desc: 导出学员报到表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-02 16:55:46
#      History:
#      Paramer:
=============================================================================*/

class exl_json extends worker {
    private $db;
    function __construct($options) {
        $powers = [
            'downloadEnrollInfo'  => '500105',
            'downloadStudentInfo' => '500105',
            'downloadArrivalInfo' => '500105',
            ];
        $this->db = new MySql();

        parent::__construct($options, [$powers[$options['act']]]);
    }

    function run() {
        $options = $this->options;
        switch ($options['act']) {
        case 'downloadEnrollInfo':$this->downloadEnrollInfo($options); break;
        case 'downloadStudentInfo':$this->downloadStudentInfo($options); break;
        case 'downloadArrivalInfo':$this->downloadArrivalInfo($options); break;
        default: break;
        }
    }

    private function builterHeard(){
        $filename = "cmb".time().".xls";
        header("Content-type:application/vnd.ms-excel");
        Header("Accept-Ranges:bytes");
        Header("Content-Disposition:attachment;filename=".$filename); //$filename导出的文件名
        header("Pragma: no-cache");
        header("Expires: 0");
        $heard =  '<html xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns="http://www.w3.org/TR/REC-html40">
            <head>
            <meta http-equiv="expires" content="Mon, 06 Jan 1999 00:00:01 GMT">
            <meta http-equiv=Content-Type content="text/html; charset=gb2312">
            <!--[if gte mso 9]><xml>
            <x:ExcelWorkbook>
            <x:ExcelWorksheets>
            <x:ExcelWorksheet>
            <x:Name></x:Name>
            <x:WorksheetOptions>
            <x:DisplayGridlines/>
            </x:WorksheetOptions>
            </x:ExcelWorksheet>
            </x:ExcelWorksheets>
            </x:ExcelWorkbook>
            </xml><![endif]-->
            </head>';

        return $heard;
    }

    //下载学员报到表
    private function downloadStudentInfo($options){
        $heard = $this->builterHeard();
        $heard .=  "<table><tr>
           <th>".mb_convert_encoding("报名编号", "GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("姓名","GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("学员账号","GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("报名时间","GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("报到时间", "GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("身份证","GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("手机号","GBK","UTF-8")."</th>
           <th>".mb_convert_encoding("所属分组","GBK","UTF-8")."</th>
           </tr>";

        $clID = $options['clID'] ? $options['clID'] : $options['tse_classId'];
        $where = " WHERE cs_classId='{$clID}'";

        if(isset($options['team']) && !empty($options['team'])){
            $where .= " AND cs_team='".$options['cs_team']."'";
        }

        switch($options['hasCertNum']){
        case 1 : $where .= " AND um.certNum<>''"; break;
        case -1 : $where .= " AND (um.certNum IS NULL OR um.certNum='')"; break;
        default:break;
        }

        $sql = "SELECT cs.cs_id,cs.cs_createTime,cs.cs_studentId,cs.cs_team,um.mobile,um.trueName,um.username,um.certNum,tse.tse_id,tse.tse_createTime FROM tang_class_student cs
            LEFT JOIN tang_ucenter_member um ON um.id=cs.cs_studentId
            LEFT JOIN tang_student_enroll tse ON tse.tse_userId=cs.cs_studentId AND cs.cs_classId=tse.tse_classId $where AND tse_state=1 AND tse_status=2";

        $data = $this->db->getAll($sql);

        foreach($data as $k=>$v){
            //$v['certNum']  = F::hidIDCnum($v['certNum']);
            //$v['certNum']  = substr_replace($v['certNum'],'******',6,8);
            //$v['mobile']   = F::hidtel($v['mobile']);
            if (empty($v['cs_team'])) {
                $v['team'] = '未分组';
                $fontColor = 'red';
            }else{
                $v['team'] = "第{$v['cs_team']}组";
            }
            $heard .= "<tr>";
            $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['tse_id']."</td>";
            $heard .= "<td>".mb_convert_encoding( $v['trueName'],"GBK","UTF-8")."</td>";
            $heard .= "<td>".mb_convert_encoding( $v['username'],"GBK","UTF-8")."</td>";
            $heard .= "<td>".$v['tse_createTime']."</td>";
            $heard .= "<td>".$v['cs_createTime']."</td>";
            $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['certNum']."</td>";
            $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['mobile']."</td>";
            $heard .= "<td >".mb_convert_encoding($v['team'],"GBK","UTF-8")."</td>";
            $heard .= "</tr>";
        }
        $heard .= "</table>";
        die($heard);
    }

    //下载报名订单
    function downloadEnrollInfo($options){
        $heard = $this->builterHeard();

        $heard .=  "<table><tr>
            <th>".mb_convert_encoding("报名编号","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("会员名","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("姓名","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("手机号","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("身份证","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("报名时间","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("报名费","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("实付金额","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("班级编号","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("班级名称","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("分组","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("状态","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("审核状态","GBK","UTF-8")."</th>
            </tr>";

        $where = ' WHERE 1 ';

        if (!empty($options['cl_number'])) {
            $where .= " AND cl_number='{$options['cl_number']}' ";
        }

        if (!empty($options['cl_name'])) {
            $where .= " AND cl_name='{$options['cl_name']}' ";
        }

        if ('' != $options['tse_status']) {
            $where .= " AND tse_status='{$options['tse_status']}' ";
        }

        if ('' != $options['tse_state']) {
            $where .= " AND tse_state='{$options['tse_state']}' ";
        }

        $sql = "SELECT tse_id,tse_classId,tse_fee,tse_payFee,tse_state,tse_status,tse_createTime,cl_name,cl_state,um.trueName,um.username,
            um.certNum,mobile,tse_team,tse_station,tse_arrivalTime
            FROM tang_student_enroll tse LEFT JOIN tang_class cl ON cl.cl_id=tse.tse_classId LEFT JOIN tang_ucenter_member um ON tse.tse_userId=um.id
            $where ORDER BY tse_team ASC,tse_createTime ASC";

        $data = $this->db->getAll($sql);

        $state = array(-1=>'未通过','待审核','通过');
        $status = array(-1=>'关闭','未付款','已付款', '已报到', '已转让');
        $colorList = array('-1'=>'#D91E18','#D91E18 ','#26C281','#3598dc');

        foreach($data as $v){
            $v['state']  = $state[$v['tse_state']];
            $v['status'] = $status[$v['tse_status']];
            //$v['mobile']     = F::hidtel($v['mobile']);
            $v['tse_payfee'] = floatval($v['tse_payfee']);
            //$v['certNum']  = empty($v['certNum']) ? '无身份证信息' : substr_replace($v['certNum'],'******',6,8);
            $v['certNum']  = empty($v['certNum']) ? '' : $v['certNum'];
            $v['team'] =  empty($v['tse_team']) ? '未分组' : "第{$v['tse_team']}组";

            $heard .= "<tr>";
            $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['tse_id']."</td>";
            $heard .= "<td>".mb_convert_encoding( $v['username'],"GBK","UTF-8")."</td>";
            $heard .= "<td>".mb_convert_encoding( $v['trueName'],"GBK","UTF-8")."</td>";
            $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['mobile']."</td>";
            $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['certNum']."</td>";
            $heard .= "<td>".$v["tse_createTime"]."</td>";
			$heard .= "<td style='vnd.ms-excel.numberformat:#,##0.00'>".$v['tse_fee']."</td>";
			$heard .= "<td style='vnd.ms-excel.numberformat:#,##0.00'>".$v["tse_payfee"]."</td>";
            $heard .= "<td>".$v["tse_classId"]."</td>";
            $heard .= "<td>".mb_convert_encoding($v["cl_name"],"GBK","UTF-8")."</td>";
            $heard .= "<td>".mb_convert_encoding($v["team"],"GBK","UTF-8")."</td>";
            $heard .= "<td style='color:{$colorList[$v['tse_status']]}'>".mb_convert_encoding($v["status"],"GBK","UTF-8")."</td>";
            $heard .= "<td style='color:{$colorList[$v['tse_state']]}'>".mb_convert_encoding($v["state"],"GBK","UTF-8")."</td>";
            $heard .= "</tr>";
        }
        $heard .= "</table>";
        die($heard);
    }

    //下载接站信息
    function downloadArrivalInfo($options){
        $heard = $this->builterHeard();
        $heard .=  "<table><tr>
            <th>".mb_convert_encoding("所属分组","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("联系人","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("联系电话","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("班级","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("报名时间","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("接站地点","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("接站时间","GBK","UTF-8")."</th>
            <th>".mb_convert_encoding("接站人数","GBK","UTF-8")."</th>
            </tr>";

        $where = ' WHERE 1 ';

        if (!empty($options['cl_number'])) {
            $where .= " AND cl_number='{$options['cl_number']}' ";
        }

        $where .= " AND tse_state=1 AND tse_status IN(1,2) AND (tse_arrivalTime IS NOT NULL OR tse_station IS NOT NULL)";

        $sql = "SELECT tse.tse_id,tse.tse_arrivalTime,tse.tse_station,tse.tse_counts,cl_name,um.trueName,um.username,mobile,tse_team,tse.tse_createTime
            FROM tang_student_enroll tse LEFT JOIN tang_class cl ON cl.cl_id=tse.tse_classId LEFT JOIN tang_ucenter_member um ON tse.tse_userId=um.id
            $where ORDER BY tse.tse_team ASC";

        $data = $this->db->getAll($sql);

        $arrivaInfo = [];
        foreach ($data as $val) {
            $arrivaInfo[$val['tse_team']][] = $val;
        }

        unset($val);
        foreach($arrivaInfo as $team){
            foreach ($team as $k=>$v) {
                $v['team'] =  empty($v['tse_team']) ? '未分组' : "第{$v['tse_team']}组";
                $heard .= "<tr>";
                if ($k == 0) {
                    $rowSpan = count($team);
                    $heard .= "<td rowspan='$rowSpan'>".mb_convert_encoding( $v['team'],"GBK","UTF-8")."</td>";
                }
                $heard .= "<td>".mb_convert_encoding( $v['trueName'],"GBK","UTF-8")."</td>";
                $heard .= "<td style='vnd.ms-excel.numberformat:@'>".$v['mobile']."</td>";
                $heard .= "<td>".mb_convert_encoding($v["cl_name"],"GBK","UTF-8")."</td>";
                $heard .= "<td>".mb_convert_encoding($v["tse_createTime"],"GBK","UTF-8")."</td>";
                $heard .= "<td>".mb_convert_encoding($v["tse_station"],"GBK","UTF-8")."</td>";
                $heard .= "<td>".mb_convert_encoding($v["tse_arrivalTime"],"GBK","UTF-8")."</td>";
                $heard .= "<td>{$v['tse_counts']}</td>";
                $heard .= "</tr>";
            }
        }
        $heard .= "</table>";
        die($heard);
    }
}
