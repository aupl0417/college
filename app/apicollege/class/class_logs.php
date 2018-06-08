<?php
/**
 * Created by PhpStorm.
 * User: bayayun
 * Date: 2016/3/24
 * Time: 13:45
 */
class logs
{
    /**
     * 添加表单历史记录
     * @param $db 数据库
     * @param $fagid  工单id
     * @param $flowid 工单流程id
     * @param $form 表单内容
     * @param $faid 步骤ID
     * @param int $state 操作状态:如果多分支,那么1即为通过,-1为不通过
     * @param string $file 相关文件
     * @param string $memo 备注
     * @return int
     */
    static public function writeLogflow($db,$fagid,$flowid,$form,$faid,$state = 0, $file = '',$memo = ''){
        if(empty($fagid) || empty($db) || empty($flowid) || empty($form) || empty($faid)){
            return -1;
        }
        $history = array(
            'fh_flowid' => $flowid,
            'fh_fid' => $fagid,
            'fh_state' => $state,
            'fh_time' => F::mytime(),
            'fh_eid' => $_SESSION['userID'],
            'fh_file' => $file,
            'fh_memo' => $memo,
            'fh_form' => $form,
            'fh_faid' => $faid
        );
        return $db->insert('t_flow_history', $history);

    }
    static public function getAction($faId){
        if(empty($faId)){
            return array();
        }
        $db = new MySql();
        $sql = "SELECT fa_name FROM t_flow_action WHERE fa_id = '".$faId."'";
        $result = $db->getRow($sql);
        return !empty($result) ? $result['fa_name'] : array();

    }

}