<?php

/**
 *
 * 处理业务逻辑类（没有订单关联）
 *
 * @author vc
 *
 * @time 2016-01-29
 *
 */
class busNoOrder extends account{

    public function __construct($db = NULL) {
        if(!$this->db){
            parent::__construct(is_null($db) ? new MySql() : $db,F::mytime());
        }
    }

    /**
     * 红积分转换佣金
     * $redScroe    兑换的分值
     * $userId      用户ID
    */
    public function exchangeRedScroe($redScroe,$userId){
        //税费
        $redScore2misMoney_tax_rat = attrib::getSystemParaByKey('redScore2misMoney_tax_rat');//红积分转预存款的比例
        $redScore2misMoney_tax_rat_digits = attrib::getSystemParaByKey('redScore2misMoney_tax_rat_digits');//红积分转预存款保留小数位数
        $taxMoney =   F::bankerAlgorithm(F::bankerAlgorithm($redScroe,0.01),$redScore2misMoney_tax_rat,$redScore2misMoney_tax_rat_digits+1);

        //检查用户的货款是否足够
        $myRedScore = $this->db->getField("SELECT ac_redScore FROM t_account WHERE ac_id = '".$userId."'");
        if($myRedScore < $redScroe){
            $this->error = 'notEnoughRedScroe';  //红积分兑换失败
            return false;
        }
        if(!$this->transferCross('311',$userId,6,$userId,3,$redScroe,0,0,2,'','红积分兑换'.F::L('accountType3').'资金')){
            $this->error = 'exchangeRedScroeErr';  //红积分兑换失败
            return false;
        }
        $lastId = $this->getLastId();
        //扣10%稅
        if(!$this->transferCash('231',$userId,3,$userId,4,$taxMoney,0,0,2,'',F::L('accountType3').'10%'.F::L('accountType4'))){
            $this->error = 'exchangeRedScroeTaxErr';  //红积分兑换扣税失败
            return false;
        }
        //日志记录
        log::writeLogMongo('40104', '', $lastId, '红积分兑换'.F::L('accountType3').'资金');
        return true;
    }

    /**
     * 积分券录入
    */
    public function userTicket($userId,$code,$pwd,$remark=''){
        //交易号
        $o_id = F::getTimeMarkID();

        //验证券号密码(必须：状态：已激活；券类型：纸质券；使用用户：空；是否导出券：已导出)
        $where = "coup_code = '".$code."' AND coup_pwd = '".$pwd."' AND coup_isexp=1";
        $sql = "SELECT * FROM t_coupons WHERE ".$where;
        $coup_info = $this->db->getRow($sql);
        if(!$coup_info){
            $this->error = 'noTicket';
            return false;
        }
        //检查券是否可以使用
        switch($coup_info['coup_state']){
            case 0: //未激活unactive
                $this->error = 'ticketUnactive';
                return false;
                break;
            case 1: //已激活（正确）
                break;
            case 2: //已使用
                $this->error = 'ticketHasUse';
                return false;
                break;
            default:
                $this->error = 'ticketStateErr';
                return false;
                break;
        }
        //不能自己转给自己
        if($coup_info['coup_uid'] == $userId){
            $this->error = 'ticketBuyerErr';
            return false;
        }

        //备注前面加上积分券号
        $remark = $code.' 用户积分券录入：'.$remark;
        //业务类型
        $busi_type = '122';
        //每张券都加入提成嘉奖
        $ins_val = array(
            'bu_id'		=> $o_id,
            'bu_type'	=> $busi_type,
            'bu_money'	=> F::bankerAlgorithm($coup_info['coup_point'], 0.01),
            'bu_buyUid'	=> $userId,
            'bu_sellUid'=> $coup_info['coup_uid'],
            'bu_createTime'		=>date('Y-m-d H:i:s', time()),
            'bu_returnPercent'	=>1,
            'bu_state'	=> 3,
            'bu_isQF'		=> 1,
            'bu_delayDate'		=> date('Y-m-d H:i:s', time()),//加入全返时间
            'bu_memo'	=> $remark,
            'bu_express'=> 0
        );
        $rs = $this->db->insert('t_order',$ins_val);
        if(!$rs){
            $this->error = 'insertOrderErr';
            return false;
        }

        //更新券表状态
        $vartab = array('coup_state'=>'2','coup_useTime'=>date("Y-m-d H:i:s",time()),'coup_reUid'=>$userId,'coup_reuNick'=>$this->db->getField("SELECT u_nick FROM t_user WHERE u_id = '".$userId."'"));
        $up_state = $this->db->update('t_coupons',$vartab,"coup_code = '".$code."'");
        if(!$up_state){
            $this->error = 'updateTicketErr';
            return false;
        }
        //券对应积分分发到用户账户中(录入时，积分流转是：平台->消费者)
        $rs = $this->transferScore($busi_type,ADMIN_ID,7,$userId,5,$coup_info['coup_point'],0,0,2,$o_id,$remark);
        if(!$rs){
            $this->error = 'updateTicketErr';
            return false;
        }

        //日志记录
        log::writeLogMongo(10101, 't_order', $remark, $ins_val);
        //返回订单号（方便计算提成嘉奖）
        return true;
    }

}
