<?php
/**
 * 账户类
 * @author Dolen
 * @version 1.0.0
 *
 */

class account {

    //数据库对象
    protected   $db;
    //余额账户
    private     $freeMoney;
    //冻结账户
    private     $frozenMoney;
    //积分帐号
    private     $score;
    //唐宝账户
    private     $tangbao;
    //库存积分
    private     $storeScore;
    //账户ID
    private     $aid;
	//时间
	protected   $time;
    //错误
    protected   $error;
    //任何操作执行完成后，判断如果结果是true，则此属性存放记录id
    private     $lastid;

    public function __construct($db = NULL, $time = NULL) {
        $this->db = is_null($db) ? new MySql() : $db;
        $this->time = is_null($time) ? F::mytime() : $time;
    }

    //创建账户
    public function createAccount($uid, $nick) {
        $p = array(
            'a_uid'         => $uid,
            'a_nick'        => $nick,
            'a_freeMoney'   => 0,
            'a_frozenMoney' => 0,
            'a_score'       => 0,
            'a_tangBao'     => 0,
            'a_storeScore'  => 0,            
            'a_state'       => 1,
            'a_createTime'  => $this->time,
			'a_isDefault'	=> 1,
            'a_memo'        => '',
        );
		$a_crc = $this->getAccountCRC($p);
		$p['a_crc'] = $a_crc;
        $result = $this->db->insert('pay_account', $p);
		if($result == 1){
			return $this->db->getLastID();
		}else{
			$this->error = -10;//账户不存在
			return false;				
		}
    }

    //计算账户CRC检验值结果
    protected function getAccountCRC($accounts) {
		$str = $accounts['a_uid'] . $accounts['a_createTime'] . ROUND($accounts['a_freeMoney']*100) . ROUND($accounts['a_frozenMoney']*100) . ROUND($accounts['a_score']*1000) . ROUND($accounts['a_tangBao']*1000) . ROUND($accounts['a_storeScore']*1000);
        return F::getSuperMD5($str);
    }

    //更新用户CRC
	/* $ids: 账户id,更新多个账户使用数组 */
    public function updateCRC($ids) {
        $sql = "UPDATE pay_account SET a_crc = MD5(CONCAT(SHA1(CONCAT(a_uid, a_createTime, ROUND(a_freeMoney*100),ROUND(a_frozenMoney*100), ROUND(a_score*1000), ROUND(a_tangBao*1000), ROUND(a_storeScore*1000))),'@$^^&!##$$%%$%$$^&&asdtans2g234234HJU')) WHERE a_id in ($ids)";
        return $this->db->exec($sql);
    }	

    //更改账户状态
    private function setAccountState($aid, $state) {
        return $this->db->update('pay_account', array('a_state' => $state), "a_id='$aid'");
    }

    //根据账户ID 获取账户信息
	/* $uid:会员id
	   $aid:账户id,如果不传账户id,那么取用户的默认账户	
	*/
    private function getAccountInfo ($uid, $aid=''){
		if($aid == ''){			
			$sql = "SELECT a_id FROM pay_account WHERE a_uid = '$uid' and a_isDefault = '1' LIMIT 1";
			$aid = $this->db->getField($sql);
			if(!$aid){
				$this->error = -20;//账户不存在
				return false;					
			}
		}
			
		$sql = "SELECT * FROM pay_account WHERE a_id = '$aid' AND a_uid = '$uid' FOR UPDATE";
		$result = $this->db->getRow($sql);
		if(!$result){
			$this->error = -21;//账户不存在
			return false;			
		}
        return $result;
    }

    //检查crc校验账户数据是否正确
    private function checAccountkCRC($account) {      
        $ret = $this->getAccountCRC($account);

        if ($ret != $account['a_crc']) {
            $this->setAccountState($account['a_id'], 0); //冻结账户
			$this->error = -11;//crc错误,冻结账户
			return false;
        }
        return true;
    }
	
	
    //现金异动操作方法
	/* 
	 * $busid:异动类型
	 * $uid:用户id
	 * $unick:用户名
	 * $aid: 账户ID,留空则取默认账户
	 * $type: -1:减; 1加
	 * $money: 金额
	 * $opid: 经办人
	 * $channel: 通道
	 * $orderId: 订单id
	 * $memo: 备注
	 * $source: 来源id,用于计算收益来源
	 * $sourceUser: 来源,同上
	 */
    public function transferCash($busid, $uid, $unick, $aid = '', $type, $money, $opid = '', $channel = 0, $orderId = '', $memo = '', $source = '', $sourceUser = '') {
        $u = $this->getAccountInfo($uid);
        if(!$this->checAccountkCRC($u)){
            return false;
        }
        $aid = $u['a_id'];
        $u['a_freeMoney'] = $u['a_freeMoney'] + $money * $type;//得到本次变化后的账户余额
        if(($u['a_freeMoney'] < 0) || ($money <= 0)){//如果本次变动后的账户余额小于0或者变动金额小于等于零//返回错误
            $this->error = -30;
            return false;			
		}
		
		/* 如果不是雇员后台操作或者工单服务费,账户转出要做限制 */
		if($busid != '10290' && substr($busid, 0, 5) != '10299' && $busid != '10287'){
			if(F::isEmpty($u['a_payPwd']) && $type == -1){//如果账户转出,且支付密码为空,返回错误
				$this->error = -33;
				return false;						
			}
			
			if($u['a_state'] != 1 && $type == -1){//账户被冻结或注销不能转出
				$this->error = -34;
				return false;			
			}
		}
		
		$u['a_crc'] = $this->getAccountCRC($u);//得到新的crc校验码

        //插入异动记录
        $this->lastid = empty($this->lastid) ? F::getTimeMarkID() : $this->lastid;		
		$p = array(
		'ca_id' 		=> $this->lastid,
		'ca_uid' 		=> $uid,
		'ca_unick' 		=> $unick,
		'ca_aid' 		=> $aid,			
		'ca_money' 		=> $money,
		'ca_businessId' => $busid,
		'ca_channel' 	=> $channel,
		'ca_createTime' => $this->time,
		'ca_operId' 	=> $opid,
		'ca_orderId' 	=> $orderId,
		'ca_memo'		=> $memo,
		'ca_balance'	=> $u['a_freeMoney'],			
		'ca_source'		=> $source,
		'ca_sourceUser' => $sourceUser,
		'ca_type'		=> $type
		);

        if ($this->db->insert('pay_account_cash_tran', $p) != 1) {
            $this->error = -31;
            return false;
        }

		/* 更新账户信息 */
		if ($this->db->update('pay_account', $u, "a_id = '".$u['a_id']."'") != 1) {
			$this->error = -32;
			return false;
		}
		return true;		
    }	
	
    //冻结资金异动操作方法
	/* 
	 * $busid:异动类型
	 * $uid:用户id
	 * $unick:用户名
	 * $aid: 账户ID,留空则取默认账户
	 * $type: -1:减; 1加
	 * $money: 金额
	 * $opid: 经办人
	 * $channel: 通道
	 * $orderId: 订单id
	 * $memo: 备注
	 * $source: 来源id,用于计算收益来源
	 * $sourceUser: 来源,同上
	 */
    public function transferFrozen($busid, $uid, $unick, $aid = '', $type, $money, $opid = '', $channel = 0, $orderId = '', $memo = '', $source = '', $sourceUser = '') {
		$u = $this->getAccountInfo($uid);		
		if(!$this->checAccountkCRC($u)){
			return false;
		}
		$aid = $u['a_id'];
		
		$u['a_frozenMoney'] = $u['a_frozenMoney'] + $money * $type;
		if(($u['a_frozenMoney'] < 0) || ($money <= 0)){

            $this->error = -40;
            return false;			
		}
		$u['a_crc'] = $this->getAccountCRC($u);
		
        //插入异动记录
        $this->lastid = empty($this->lastid) ? F::getTimeMarkID() : $this->lastid;		
		$p = array(
		'ca_id' 		=> $this->lastid,
		'ca_uid' 		=> $uid,
		'ca_unick' 		=> $unick,
		'ca_aid' 		=> $aid,			
		'ca_money' 		=> $money,
		'ca_businessId' => $busid,
		'ca_channel' 	=> $channel,
		'ca_createTime' => $this->time,
		'ca_operId' 	=> $opid,
		'ca_orderId' 	=> $orderId,
		'ca_memo'		=> $memo,
		'ca_balance'	=> $u['a_frozenMoney'],			
		'ca_source'		=> $source,
		'ca_sourceUser' => $sourceUser,
		'ca_type'		=> $type
		);
		
        if ($this->db->insert('pay_account_frozen_tran', $p) != 1) {
            $this->error = -41;
            return false;
        }
		
		/* 更新账户信息 */
		if ($this->db->update('pay_account', $u, "a_id = '".$u['a_id']."'") != 1) {
			$this->error = -42;
			return false;
		}	
		return true;		
    }
	
    //积分异动操作方法
	/* 
	 * $busid:异动类型
	 * $uid:用户id
	 * $unick:用户名
	 * $aid: 账户ID,留空则取默认账户
	 * $type: -1:减; 1加
	 * $score: 积分
	 * $opid: 经办人
	 * $channel: 通道
	 * $orderId: 订单id
	 * $memo: 备注
	 * $source: 来源id,用于计算收益来源
	 * $sourceUser: 来源,同上
	 */
    public function transferScore($busid, $uid, $unick, $aid = '', $type, $score, $opid = '', $channel = 0, $orderId = '', $memo = '', $source = '', $sourceUser = '') {
		$u = $this->getAccountInfo($uid);		
		if(!$this->checAccountkCRC($u)){
			return false;
		}
		$aid = $u['a_id'];
		//echo "\n".json_encode(func_get_args())."\n";
		$u['a_score'] = $u['a_score'] + $score * $type;
		//echo "\n".$u['a_score'] ." + ". $score ." * ". $type."\n";
		if(($u['a_score'] < 0) || ($score <= 0)){
            $this->error = -50;
            return false;			
		}
		$u['a_crc'] = $this->getAccountCRC($u);
		
        //插入异动记录
        $this->lastid = empty($this->lastid) ? F::getTimeMarkID() : $this->lastid;		
		$p = array(
		'sc_id' 		=> $this->lastid,
		'sc_uid' 		=> $uid,
		'sc_unick' 		=> $unick,
		'sc_aid' 		=> $aid,			
		'sc_score' 		=> $score,
		'sc_businessId' => $busid,
		'sc_channel' 	=> $channel,
		'sc_createTime' => $this->time,
		'sc_operId' 	=> $opid,
		'sc_orderId' 	=> $orderId,
		'sc_memo'		=> $memo,
		'sc_balance'	=> $u['a_score'],			
		'sc_source'		=> $source,
		'sc_sourceUser' => $sourceUser,
		'sc_type'		=> $type
		);
		
        if ($this->db->insert('pay_account_score_tran', $p) != 1) {
            $this->error = -51;
            return false;
        }
		
		/* 更新账户信息 */
		if ($this->db->update('pay_account', $u, "a_id = '".$u['a_id']."'") != 1) {
			$this->error = -52;
			return false;
		}	
		return true;
    }
	
    //唐宝异动操作方法
	/* 
	 * $busid:异动类型
	 * $uid:用户id
	 * $unick:用户名
	 * $aid: 账户ID,留空则取默认账户
	 * $type: -1:减; 1加
	 * $score: 积分
	 * $opid: 经办人
	 * $channel: 通道
	 * $orderId: 订单id
	 * $memo: 备注
	 * $source: 来源id,用于计算收益来源
	 * $sourceUser: 来源,同上
	 */
    public function transferTang($busid, $uid, $unick, $aid = '', $type, $score, $opid = '', $channel = 0, $orderId = '', $memo = '', $source = '', $sourceUser = '') {
        ////--
        //$accounts = $this->getAccountInfo($uid);
        //$str = $accounts['a_uid'] . $accounts['a_createTime'] . ROUND($accounts['a_freeMoney']*100) . ROUND($accounts['a_frozenMoney']*100) . ROUND($accounts['a_score']*1000) . ROUND($accounts['a_tangBao']*1000) . ROUND($accounts['a_storeScore']*1000);
        //return F::getSuperMD5($str);
        ////--

        $u = $this->getAccountInfo($uid);		
        if(!$this->checAccountkCRC($u)){
            return false;
        }
        $aid = $u['a_id'];
		
		$u['a_tangBao'] = $u['a_tangBao'] + $score * $type;//得到本次变化后的账户余额
		if(($u['a_tangBao'] < 0) || ($score <= 0)){//如果本次变动后的账户余额小于0或者变动金额小于等于零//返回错误
            $this->error = -60;
            return false;			
		}
		
		
		/* 如果不是雇员后台操作或者工单服务费用,账户转出要做限制 */
		if($busid != '40290' && substr($busid, 0, 5) != '40299' && $busid != '40287'){
			if(F::isEmpty($u['a_payPwd']) && $type == -1){//如果账户转出,且支付密码为空,返回错误
				$this->error = -63;
				return false;						
			}		
			
			if($u['a_state'] != 1 && $type == -1){//账户被冻结或注销不能转出
				$this->error = -64;
				return false;			
			}
		}
		$u['a_crc'] = $this->getAccountCRC($u);
		
        //插入异动记录
        $this->lastid = empty($this->lastid) ? F::getTimeMarkID() : $this->lastid;		
		$p = array(
		'sc_id' 		=> $this->lastid,
		'sc_uid' 		=> $uid,
		'sc_unick' 		=> $unick,
		'sc_aid' 		=> $aid,			
		'sc_score' 		=> $score,
		'sc_businessId' => $busid,
		'sc_channel' 	=> $channel,
		'sc_createTime' => $this->time,
		'sc_operId' 	=> $opid,
		'sc_orderId' 	=> $orderId,
		'sc_memo'		=> $memo,
		'sc_balance'	=> $u['a_tangBao'],			
		'sc_source'		=> $source,
		'sc_sourceUser' => $sourceUser,
		'sc_type'		=> $type
		);
		
        if ($this->db->insert('pay_account_tang_tran', $p) != 1) {
            $this->error = -61;
            return false;
        }
		
		/* 更新账户信息 */
		if ($this->db->update('pay_account', $u, "a_id = '".$u['a_id']."'") != 1) {
			$this->error = -62;
			return false;
		}	
		return true;
    }
	
    //库存积分异动操作方法
	/* 
	 * $busid:异动类型
	 * $uid:用户id
	 * $unick:用户名
	 * $aid: 账户ID,留空则取默认账户
	 * $type: -1:减; 1加
	 * $score: 积分
	 * $opid: 经办人
	 * $channel: 通道
	 * $orderId: 订单id
	 * $memo: 备注
	 * $source: 来源id,用于计算收益来源
	 * $sourceUser: 来源,同上
	 */
    public function transferStore($busid, $uid, $unick, $aid = '', $type, $score, $opid = '', $channel = 0, $orderId = '', $memo = '', $source = '', $sourceUser = '') {
		$u = $this->getAccountInfo($uid);
		if(!$this->checAccountkCRC($u)){
			return false;
		}
		$aid = $u['a_id'];
		
		$u['a_storeScore'] = $u['a_storeScore'] + $score * $type;
		if(($u['a_storeScore'] < 0) || ($score <= 0)){
            $this->error = -70;
            return false;			
		}
		
		/* 如果不是雇员后台操作,账户转出要做限制 */
		if($busid != '50290' && substr($busid, 0, 5) != '50299'){
			if(F::isEmpty($u['a_payPwd']) && $type == -1){//如果账户转出,且支付密码为空,返回错误
				$this->error = -73;
				return false;						
			}		
			
			if($u['a_state'] != 1 && $type == -1){//账户被冻结或注销不能转出
				$this->error = -74;
				return false;			
			}
		}
		
		$u['a_crc'] = $this->getAccountCRC($u);
		
        //插入异动记录
        $this->lastid = empty($this->lastid) ? F::getTimeMarkID() : $this->lastid;		
		$p = array(
		'sc_id' 		=> $this->lastid,
		'sc_uid' 		=> $uid,
		'sc_unick' 		=> $unick,
		'sc_aid' 		=> $aid,			
		'sc_score' 		=> $score,
		'sc_businessId' => $busid,
		'sc_channel' 	=> $channel,
		'sc_createTime' => $this->time,
		'sc_operId' 	=> $opid,
		'sc_orderId' 	=> $orderId,
		'sc_memo'		=> $memo,
		'sc_balance'	=> $u['a_storeScore'],			
		'sc_source'		=> $source,
		'sc_sourceUser' => $sourceUser,
		'sc_type'		=> $type
		);
		
        if ($this->db->insert('pay_account_store_tran', $p) != 1) {
            $this->error = -71;
            return false;
        }
		
		/* 更新账户信息 */
		if ($this->db->update('pay_account', $u, "a_id = '".$u['a_id']."'") != 1) {
			$this->error = -72;
			return false;
		}	
		return true;
    }


    //从外部设置异动ID
	public function setTransId($id){
		$this->lastid = $id;
	}

    public function getError() {
		return $this->error;
    }

    public function getLastId() {
        return $this->lastid;
    }

}
