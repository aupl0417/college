<?php

/**
 *
 * 支付逻辑类
 *
 * @author vc
 *
 * @time 2016-01-21
 *
 * $pay_error： -1=>账户被冻结/已注销；
 *              -2=>非未付款订单；-20=>订单更新失败
 *              -3=>预付款不足；-30=>预付款支付失败；
 *              -4=>货款不足；-40=>货款支付失败；-41=>货款服务费扣除失败；
 *              -5=>唐宝不足；-50=>唐宝支付失败；-51=>兑换唐宝相应预存款失败
 *              -6=>普通账号未实名认证；-60=>企业账号未实名认证；-61=>账号错误
 *
 */
class pay extends account{

    public function __construct($db = NULL) {
        if(!$this->db){
            parent::__construct(is_null($db) ? new MySql() : $db,F::mytime());
        }
    }

    /**
     * 支付方法
     * $orderId  相应的订单ID
     * $type     支付类型（1=>预存款；2=>交易货款；3=>唐宝（需要认证）；）
     */
    public function payForOrder($orderId,$type){
        //检查订单状态
        $orderInfo = $this->db->getRow("SELECT * FROM t_order WHERE bu_id = '".$orderId."'");
        if(!$orderInfo || ($orderInfo['bu_state'] != 0)){  //非未付款订单
            $this->error = 'notUnPayOrder';
            return false;
        }
        //支付
        if(!$this->payAction($type,$orderInfo)){
            return false;
        }

        //更新订单状态
        if(!$this->updateOrder($orderInfo['bu_id'])){
            $this->error = 'updateOrderStateErr';  //更新失败
            return false;
        }
        return true;
    }

    //支付调用
    private function payAction($type,$orderInfo){

        //检查账户信息
        $accInfo = $this->db->getRow("SELECT * FROM t_account WHERE ac_id = '".$orderInfo['bu_buyUid']."'");
        //如果冻结/注销了，无法支付
        if(!$accInfo || ($accInfo['ac_state'] != 1)){
            $this->error = 'accountErr';  //账户被冻结/已注销
            return false;
        }

        switch($type){
            case 1:
                //预付款支付
                $rs = $this->freeMoney2pay($orderInfo,$accInfo['ac_freeMoney']);
                if(!$rs){
                    return false;
                }
                break;
            case 2:
                //交易款支付
                $rs = $this->busMoney2pay($orderInfo,$accInfo['ac_busMoney']);
                if(!$rs){
                    return false;
                }
                break;
            case 3:
                //唐宝转预付款
                $rs = $this->tangbao2pay($orderInfo['bu_id'],$orderInfo['bu_buyUid'],$orderInfo['bu_money'],$orderInfo['bu_memo'],$accInfo['ac_tangbao']);
                if(!$rs){
                    return false;
                }
                //用预付款支付
                $rs = $this->freeMoney2pay($orderInfo,$accInfo['ac_freeMoney']);
                if(!$rs){
                    return false;
                }
                break;
            default:
                return false;
                break;
        }
        return true;
    }


    /**
     * 预付款支付
     * $data    订单信息
     * $freeMoney   用户的预付款总额
    */
    public function freeMoney2pay($data,$freeMoney=''){
        if(!$freeMoney){
            //检查用户的货款是否足够
            $freeMoney = $this->db->getField("SELECT ac_freeMoney FROM t_account WHERE ac_id = '".$data['bu_buyUid']."'");
        }

        if($freeMoney < $data['bu_money']){
            $this->error = 'freeMoneyShortage';  //预付款不足
            return false;
        }

        //付款
        if(!$this->transferCash($data['bu_type'],$data['bu_buyUid'],1,ADMIN_ID,1,$data['bu_money'],0,0,2,$data['bu_id'],$data['bu_memo'])){
            $this->error = 'freeMoneyPayErr';  //预付款支付失败
            return false;
        }
        return true;
    }

    /**
     * 货款支付
     * $data    订单信息
     * $busMoney   用户的货款总额
    */
    public function busMoney2pay($data,$busMoney=''){
        if(!$busMoney){
            //检查用户的货款是否足够
            $busMoney = $this->db->getField("SELECT ac_busMoney FROM t_account WHERE ac_id = '".$data['bu_buyUid']."'");
        }

        //货款支付服务费比例
        $busMoney_pay_rat = attrib::getSystemParaByKey('busMoney_pay_rat');
        //货款支付服务费保留小数位数
        $busMoney_pay_rat_digits = attrib::getSystemParaByKey('busMoney_pay_rat_digits');
        //所需的货款
        $pay_money = F::bankerAlgorithm($data['bu_money'],1+$busMoney_pay_rat,$busMoney_pay_rat_digits+1);
        if($busMoney < $pay_money){
            $this->error = 'busMoneyShortage';  //货款不足
            return false;
        }

        //付款
        if(!$this->transferCash($data['bu_type'],$data['bu_buyUid'],2,ADMIN_ID,2,$data['bu_money'],0,0,2,$data['bu_id'],$data['bu_memo'])){
            $this->error = 'busMoneyPayErr';  //货款支付失败
            return false;
        }
        //服务费
        if($busMoney_pay_rat > 0){
            if(!$this->transferCash($data['bu_type'],$data['bu_buyUid'],2,$data['bu_buyUid'],4,($pay_money - $data['bu_money']),0,0,2,$data['bu_id'],$data['bu_memo'].'服务费')){
                $this->error = 'busMoneyPayServiceFeeErr';  //货款转服务费扣除失败
                return false;
            }
        }
        return true;
    }

    /**
     * 使用唐宝支付（唐宝直接转换相应的预付款）
     * $orderID    订单ID
     * $buyer       用户ID
     * $tangbaoMoney   使用唐宝支付的金额
     * $memo        订单备注
     * $tangbao    账户的唐宝值（判断是否足够唐宝支付）
     *
     * 注意：唐宝消费需要实名认证
    */
    public function tangbao2pay($orderID,$buyer,$tangbaoMoney,$memo='',$tangbao=''){
        //检查是否已经实名认证
        $user = new user();
        $userInfo = $user->getFulluserInfo($buyer);
        //校验是否实名认证
        if($userInfo['u_type'] == 0){
            if(!$userInfo['userAuthInfo']['person']['authed']){
                $this->error = 'personalUnAuth';  //个人账号未实名认证
                return false;
            }
        }else if($userInfo['u_type'] == 1){
            if(!$userInfo['userAuthInfo']['company']['authed']){
                $this->error = 'companyUnAuth';  //企业账号未实名认证
                return false;
            }
        }else{
            $this->error = 'userErr';  //账号错误
            return false;
        }

        if(!$tangbao){
            //检查用户的唐宝是否足够
            $tangbao = $this->db->getField("SELECT ac_tangbao FROM t_account WHERE ac_id = '".$buyer."'");
        }

        //唐宝转预存款的比例
        $tangbao2freeMoney_tax_rat = attrib::getSystemParaByKey('tangbao2freeMoney_tax_rat');
        //唐宝转预存款保留小数位数
        $tangbao2freeMoney_tax_rat_digits = attrib::getSystemParaByKey('tangbao2freeMoney_tax_rat_digits');

        //所需唐宝数
        $need_red = F::bankerDIv($tangbaoMoney*100, 1-$tangbao2freeMoney_tax_rat,$tangbao2freeMoney_tax_rat_digits+1);
        if($need_red > $tangbao){
            $this->error = 'tangbaoShortage';  //唐宝不足
            return false;
        }
        /*---------唐宝兑换相应预付款 start------------*/
        //转唐宝给ADMIN
        if (!$this->transferScore('140',$buyer, 6, ADMIN_ID, 6,  $need_red, 0,0, 2,$orderID, $memo.'---唐宝消费')) {
            $this->error = 'redPayErr';  //唐宝支付失败
            return false;
        }
        //admin转相应的预存款到用户
        $f_money = F::bankerAlgorithm($need_red,0.01);
        if(!$this->transferCash('140',ADMIN_ID,1, $buyer,1, $f_money, 0 ,0,2,$orderID,$memo.'---唐宝消费 兑换对应的预付款')){
            $this->error = 'tangbaoPayErr';  //兑换唐宝相应预存款失败
            return false;
        }
        //减去购买需要的金额，多余的转到税费账户
        $tax_money = $f_money - $tangbaoMoney;
        if(!$this->transferCash('234',$buyer,1,$buyer,4,$tax_money,0,0,2,$orderID,$memo.'---唐宝兑换预存款的10%'.F::L('accountType4').',唐宝消费')){
            $this->error = 'tangbaoPayErr';  //兑换唐宝相应预存款失败
            return false;
        }
        /*---------唐宝兑换相应预付款   end------------*/
        return true;
    }
	/**
     * 检测支付密码是否正确
     * @param $pwd
     * @param $userid
     * @return bool
     */
    public function checkpayPwd($pwd,$userid=0){
        if(empty($pwd)){
            return false;
        }
        $uid = !empty($userid) ? $userid : $_SESSION['userID'];
        //验证支付密码
        $payPwd = F::getSuperMD5($pwd);
        $sql = "SELECT u_payPwd FROM t_user WHERE u_id = '".$uid."'";
          $userAry = $this->db->getRow($sql);
		if(!$userAry){
			return false;
		}		
        return ($payPwd ==  $userAry['u_payPwd']);
	}	
    //更新订单状态
    private function updateOrder($orderID){
        $vartab = array('bu_state'=>1, 'bu_isQF'=>1);
        return $this->db->update('t_order',$vartab,"bu_id = '".$orderID."'");
    }
}
