<?php
/*=============================================================================
#     FileName: index.php
#         Desc: 排课表（按月显示）
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:45:18
#      History:
#      Paramer:
=============================================================================*/
/**
 * @api                    {post}/courseSchedule/index.json 排课表（按月显示）
 * @apiDescription         发起修改邮箱工单
 * @apiName                index_json
 * @apiGroup               courseSchedule
 *
 * @apiParam {int}   flowID    工单分类     必须
 *
 * @apiSuccessExample      Success-Response:
 *{
 * "id": "1001",
 * }
 *
 * @apiErrorExample        Error-Response:
 *{
 * "id": "1002",
 * }
 *
 */

class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50010101]);
    }

    function run() {
        $data = array(
           //'code'   => '50010101',
           'code'   => '10101',
           'events' => '',
           'today'   => date('Y-m-d'),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
