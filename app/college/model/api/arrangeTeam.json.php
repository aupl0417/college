<?php
/*=============================================================================
#     FileName: arrangeTeam.json.php
#         Desc: 报到自动分组
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-17 11:41:05
#      History:
#      Paramer: 
=============================================================================*/
/**
 * @api                    {post} /api/arrangeTeam.json 报到自动分组
 * @apiDescription         报到自动分组
 * @apiName                sendEnrollCheckSms_json
 * @apiGroup               ENROLL
 * @apiPermission          wuyuanahang
 *
 * @apiParam {int}     classId      班级ID
 *
 * @apiSuccessExample      Success-Response:
	 *	{
	 code: "1001",
	 data: [
         'team'=>1
	 ]
	 }
 *
 */

class arrangeTeam_json extends api {
    function run() {

        $options = $this->options;
        $classId = intval($options['classId']);
        $db      = new MySql();

        $res = ['team'=>1];

        $classTeamInfo = $db->getRow("SELECT cl_teamNum FROM tang_class WHERE cl_id='{$classId}'");

        if (empty($classTeamInfo)) {
            return apis::apiCallback('1001',$res); 	
        }

        $teamList = range(1,$classTeamInfo['cl_teamNum']);
        $teamList = array_fill_keys($teamList,0);

        $sql = "SELECT COUNT(1) num,tse_team FROM tang_student_enroll
            WHERE tse_classId='{$classId}' AND tse_state=1 AND tse_status<>-1 AND tse_team<>0 GROUP BY tse_team";

        $teamSituation = $db->getAll($sql);

        if(!empty($teamSituation)){
            $teamSituation = array_column($teamSituation,'num','tse_team');
            $teamList = $teamSituation + $teamList;
            $minNum = min($teamList);
            $res['team'] = array_search($minNum,$teamList);
        }

        return apis::apiCallback('1001', $res);
    }
}
