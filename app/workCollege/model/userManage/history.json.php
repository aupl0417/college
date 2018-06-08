<?php
/***
 * 用户操作日志
 * Auther:JoJoJing
 * Time： 2016-07-29
 * @param type 【0 用户操作日志，1 雇员操作日志】
*/

class history_json extends worker {
	function __construct($options) {
	    $type = !isset($options['type']) ? 0 : $options['type'];
	    $power = array(
	        '0' => 6010701,  //用户操作日志权限
	        '1' => 6010702,  //账户操作权限
	    );
	    parent::__construct($options, [$power[$type]]);
    }

    function run() {
        $db        = new MySql();
        $mongoLogs = new mongoLogs();
        $type      = !isset($this->options['type']) ? 0 : $this->options['type'];
        $draw      = !isset($this->options['draw']) ? 0 : $this->options['draw'];

        $id = isset($this->options['id']) ? $this->options['id'] : '';
        if(empty($id) && strlen($id) != 32){
            $this->show(message::getFormatMsg('1002','参数错误'));
            exit;
        }

        if($type == 0){ //用户操作日志
            //注册日志【20160003,20160004】/个人资料完善【20160002】/认证日志【502】/会员升级【503，1020209,4020209,1020201,1020202,4020201,4020202,upgrade】/用户登录【1112】/会员资料修改【50101，t_user/t_user_person/t_user_company】
            //安全设置【登录密码 50102 t_user 、支付密码 50102 pay_account,找回支付密码 50102 pay_account】
            //会员发起工单【50801】/商家设置库存积分提醒功能【10211】个人升级企业【609】

            //雇员-修改会员资料【like->60110%】/冻结【60102】/解冻【60103】/重置密码【60105】/会员降级【60113】
            //个人认证，企业认证审核【60201,60202】/联盟商家申请【605】/联盟商家审核【60502】/雇员发起工单【30601】

            $orWhere = array(
                'log_user'      =>  $id, //会员ID
                'log_r_id'      =>  $id,
            );
            $likeWhere = '60110';  //模糊查询用
            $inWhere = array(20160003,20160004,20160002,502,503,1020209,4020209,1020201,1020202,4020201,4020202,1112,50101,50102,50801,10211,60102,60103,60105,60113,60201,60202,605,60502,30601,609,($regexM['log_r_id'] = new \MongoRegex('/'.$likeWhere.'/i')));
            $logList = $mongoLogs->logList($this->options,$inWhere,'',$orWhere);

            foreach($logList as $k => $v){
                $logList[$k]['DT_RowId'] = 'row_'.$v['log_code'];  //这一行是必须加上的,用于给table的tr加上标识;请使用主键或者unqiue字段
                $logList[$k]['log_memo'] = isset($v['log_change']['memo']) ? $v['log_change']['memo'] : (isset($v['log_change']['au_memo']) ? $v['log_change']['au_memo'] : (isset($v['log_change']['bu_memo']) ? $v['log_change']['bu_memo'] : '-'));
                $logList[$k]['log_change'] = "<textarea id='".$v['log_code']."'>".json_encode((isset($v['log_change']) ? $v['log_change'] : ''))."</textarea>";
                $logList[$k]['log_time'] = date('Y-m-d H:i:s',$v['log_time']);
                if(!empty($v['log_user'])){
                    if(strlen($v['log_user']) == 32){
                        $sql = "select u_nick from t_user where u_id = '".$v['log_user']."'";
                        $logUser = $db->getField($sql);
                    }elseif(strlen($v['log_user']) == 9){
                        $sql = "select e_name from t_employee where e_id = '".$v['log_user']."'";
                        $logUser = $db->getField($sql);
                    }else{
                        $logUser = '-';
                    }
                }else{
                    $logUser = '-';
                }
                $logList[$k]['log_user'] = $logUser;
                $logList[$k]['op'] = '<a href="/userManage/historyDetails/?_ajax=1&typeId='.$v['log_type_id'].'&_id='.$v['_id'].'" data-target="#temp-modal-history" data-toggle="modal" class="text-nowrap btn-xs blue"><i class="fa fa-search"></i>详情</a>';
            }
            //总记录数
            $recordsFiltered = $mongoLogs->countLog($this->options,$inWhere,'',$orWhere);

        }
        else{  //账户操作日志
            //购买代理【10304】/提现【40102】/唐宝兑换【40104】/银行转账【40106】/添加、删除支付账户（第三方平台，银行卡）【40201】【40202】
            //在线充值 设置为已到账【70202】/线下银行卡转账审核【713】/资金流转【705】/商家积分分发【10208】/购买库存积分【10207】
            //充值【40101】/冻结【7010101】解冻账户【7010102】
            $where = array(
                'log_user' => array('value' => $id),
            );
            $inWhere = array(10304,40102,40104,40106,40201,40202,70202,713,705,10208,10207,40101,7010101,7010102);
            $logList = $mongoLogs->logList($this->options,$inWhere,$where);
            foreach($logList as $k => $v){
                $logList[$k]['DT_RowId'] = 'row_'.$v['log_code'];
                $logList[$k]['log_memo'] = isset($v['log_change']['memo']) ? $v['log_change']['memo'] : '-';
                $logList[$k]['log_change'] = "<textarea id='".$v['log_code']."'>".json_encode($v['log_change'])."</textarea>";
                $logList[$k]['log_time'] = date('Y-m-d H:i:s',$v['log_time']);
                if(!empty($v['log_user'])){
                    if(strlen($v['log_user']) == 32){
                        $sql = "select u_nick from t_user where u_id = '".$v['log_user']."'";
                        $logUser = $db->getField($sql);
                    }elseif(strlen($v['log_user']) == 9){
                        $sql = "select e_name from t_employee where e_id = '".$v['log_user']."'";
                        $logUser = $db->getField($sql);
                    }else{
                        $logUser = '-';
                    }
                }else{
                    $logUser = '-';
                }
                $logList[$k]['log_user'] = $logUser;
                $logList[$k]['op'] = '<a href="/userManage/historyDetails/?_ajax=1&typeId='.$v['log_type_id'].'&_id='.$v['_id'].'" data-target="#temp-modal-history" data-toggle="modal" class="text-nowrap btn-xs blue"><i class="fa fa-search"></i>详情</a>';
            }
            //总记录数
            $recordsFiltered = $mongoLogs->countLog($this->options,$inWhere,$where);

        }

        $info = array(
            'draw'              => $draw,
            'recordsTotal'      => $recordsFiltered,  //限制最大的记录数
            'recordsFiltered'   => $recordsFiltered,  //总记录数
            'data'              => $logList,
        );
        echo json_encode($info);

	}
}
