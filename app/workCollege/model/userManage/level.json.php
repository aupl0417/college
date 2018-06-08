<?php

class level_json extends worker {
		
	function __construct($options) {		
        parent::__construct($options, [60106]);
		
    }

	function run() {
        $user = new user();
        $data = $this->options;

        //当前会员ID
        $id = isset($data['userid']) ? $data['userid'] : '';
        if($id == ''){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));
            exit;
        }

		//升级->差价金额
        $money = isset($data['money']) ? ($data['money'] - 0) : 0;
		//推荐奖励
		$isComm = isset($data['isComm']) ? true : false;
		//计算全返
		$isReturn = isset($data['isReturn']) ? true : false;
		//计算提成
		$isReward = isset($data['isReward']) ? true : false;
		//修改原因
        $reason = isset($data['reason']) ? $data['reason'] : ''; 

		//获取当前用户信息
		$userInfo = $user->getUserByID($id);

		//降级不能调整等级
		if ($userInfo['u_lowergrade'] ==1 ){
			$this->show(message::getJsonMsgStruct('1002', '该用户降级过，不能调整等级'));
			exit;
		}

      	//写入用户表变动表
		$changeLog = [
			'ut_uid'	  => $id,
			'ut_type'	  => 5,
			'ut_eid'	  => $_SESSION['userID'],
			'ut_ctime'	  => F::mytime(),
			'ut_oldValue' => '',
			'ut_newValue' => '',
			'ut_reason'   =>'',
		];

		$update = [];//更新用户表
		$db = new MySql();
		$ac = new account($db);
		$level = isset($data['level']) ? ($data['level'] - 0) : 0;        
		$changeLog['ut_reason'] = $reason;
		$changeLog['ut_oldValue'] = $userInfo['u_level'];
		$changeLog['ut_newValue'] = $level;

		//获取当前用户上级的同等级或大一级的用户的推广码
		$referral = $user->getRightParent($userInfo['u_fCode'],$level);
		//返回参数
		$update['u_fCode'] = $referral['u_code'];
		$update['u_fCode2'] = $referral['u_fCode'];
		$update['u_fCode3'] = $referral['u_fCode2'];
		$update['u_level'] = $level;
		if($userInfo['u_level'] < $data['level']){
			$update['u_upgrade'] = 1;
			$update['u_upgradeTime'] = F::mytime();
		}
		
		if(!$update){
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
			exit;
		}

		try{
			$db->beginTRAN();
			$db->update('t_user', $update, " u_id='".$id."'");//更新用户表
			$db->insert('t_user_tran', $changeLog);//写入用户表变动表		
			
			//记录操作日志			
			$update['memo'] = '等级调整';
			
			/* 如果补差价大于0 */
			if($money > 0){
				//插入订单表
				$o_id = F::getTimeMarkID(); //订单编号
				$ins_val = array(
					'bu_id'				=> $o_id,
					'bu_type'			=> 1020203,
					'bu_money'			=> $money,
					'bu_buyUid'			=> $id,
					'bu_sellUid'		=> ADMIN_ID,
					'bu_createTime'		=> F::mytime(),
					'bu_returnPercent'	=> 1,
					'bu_state'			=> 1,
					'bu_memo'			=> '后台补差价升级',
					'bu_isQF'			=> 2
				);

				/* 从该会员的现金账户扣除升级差价 */
				if (!$ac->transferCash('1020203', $id, $userInfo['u_nick'],'',-1,$money, $_SESSION['userID'], 0, $o_id, '补差价('.$money.'元)升级')){
					throw new Exception('补差价升级账户余额不足'.$ac->getError());
				}				
				
				/* 推荐奖励 */
				if($isComm){					
					$referral_award_rat = attrib::getSystemParaByKey('referral_award_rat');//推荐奖励的比例
					$referral_award_digits = attrib::getSystemParaByKey('referral_award_digits');//会员升级推荐奖励保留小数位数
					$referral_award_tax_rat = attrib::getSystemParaByKey('referral_award_tax_rat');//推荐奖励扣税比例
					$referral_award_tax_digits = attrib::getSystemParaByKey('referral_award_tax_digits');//推荐奖励扣税保留小数位数
					$award = F::bankerAlgorithm($money, $referral_award_rat, $referral_award_digits+1);
					$tax = F::bankerAlgorithm($award, $referral_award_tax_rat, $referral_award_tax_digits+1);
					
					//给推荐人创业账户奖励
					if($referral['u_id'] != ADMIN_ID && $award > 0){	//推荐人是admin，不用嘉奖
						if (!$ac->transferCash('220', ADMIN_ID, 3, $referral['u_id'], 3, $award, '', 0, 2, $o_id, $userInfo['u_nick'].'补差价('.$money.'元)升级，推荐奖励 '.$award."元".F::L('accountType3'), $userInfo['u_id'], $userInfo['u_nick'])){
							throw new Exception('给推荐人'.F::L('accountType3').'账户奖励'.$ac->getError());
						}						
						if($tax > 0){
							//扣10%税费
							if (!$ac->transferCash('230', $referral['u_id'], 3, $referral['u_id'], 4, $tax, '', 0, 2, $o_id, "推荐奖励 ".$money."元".F::L('accountType3')."扣除10%".F::L('accountType44'))){
								throw new Exception('给推荐人'.F::L('accountType3').'账户奖励扣除10%'.F::L('accountType4').$ac->getError());
							}
						}
					}					
				}
				
				/* 计算全返 */
				if($isReturn){
					/* 返还白积分 */
					if (!$ac->transferScore('106', ADMIN_ID, 5, $userInfo['u_id'], 5, F::bankerAlgorithm($money, 100, 2), '', 0, 2, $o_id, '补差价('.$money.'元)升级')){
						throw new Exception($ac->getError());
					}
				}
				
				/* 计算提成嘉奖 */
				if($isReward){
					$ins_val['bu_isQF'] = 1;
				}
				
				/* 写入订单 */
				if(!$db->insert('t_order',$ins_val)){
					throw new Exception('1');
				}
			}
			
			log::writeLogMongo(60106, 't_user', $id, $update);
			
			$db->commitTRAN();
			$this->show(message::getJsonMsgStruct('1001','修改成功'));
		}
		catch(Exception $e){			
			$db->rollBackTRAN();
			$this->show(message::getJsonMsgStruct('1002', $e->getMessage()));//错误						
		}
  }
}
