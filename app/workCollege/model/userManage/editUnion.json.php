<?php
/* 激活/成为联盟商家 */
class editUnion_json extends worker {
    private $isFlow;
    function __construct($options) {
        parent::__construct($options, [60131]);      
    }

    function run() {
		$none = 'T17JZvB7_T1RCvBVdK.png';
       
        $id = isset($this->options['id']) ? $this->options['id'] : '';//uid
		if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }		
        $u_companyName = isset($this->options['u_companyName']) ? trim($this->options['u_companyName']) : '';//公司名称
		if($u_companyName == ''){//公司名称必填
			$this->show(message::getJsonMsgStruct('1002'), '公司名称必填');
			exit;
		}
		
        $u_comArea = isset($this->options['u_comArea']) ? F::fmtNum(end(array_filter($this->options['u_comArea']))) : 0;//所在区域		
		if(!$u_comArea){//所在区域必填
			$this->show(message::getJsonMsgStruct('1002'), '所在区域必填');
			exit;
		}
        $u_comAddress = isset($this->options['u_comAddress']) ? trim($this->options['u_comAddress']) : '';//详细地址
		if($u_comAddress == ''){//详细地址必填
			$this->show(message::getJsonMsgStruct('1002'), '详细地址必填');
			exit;
		}
        $u_lng = isset($this->options['u_lng']) ? (F::fmtNum($this->options['u_lng']) - 0) : 0;//经度	
        $u_lat = isset($this->options['u_lat']) ? (F::fmtNum($this->options['u_lat']) - 0) : 0;//纬度	
        $u_comLeadName = isset($this->options['u_comLeadName']) ? $this->options['u_comLeadName'] : '';//经验负责人
        $u_indId = isset($this->options['u_indId']) ? F::fmtNum(end(array_filter($this->options['u_indId']))) : 0;//行业
		if(!$u_indId){//行业必填
			$this->show(message::getJsonMsgStruct('1002'), '行业必填');
			exit;
		}
        $u_comMainIndustry = isset($this->options['u_comMainIndustry']) ? $this->options['u_comMainIndustry'] : '';//主营业务
        $u_managerSelf = isset($this->options['u_managerSelf']) ? (F::fmtNum($this->options['u_managerSelf']) - 0) : 0;//经验负责人是否本人
        $uc_contractCode = isset($this->options['uc_contractCode']) ? $this->options['uc_contractCode'] : '';//合同号
        $uc_validateContractTime = isset($this->options['uc_validateContractTime']) ? $this->options['uc_validateContractTime'] : '';//合同签订时间
        $uc_tabletCode = isset($this->options['uc_tabletCode']) ? $this->options['uc_tabletCode'] : '';//牌匾号
        $uc_buyTabletTime = isset($this->options['uc_buyTabletTime']) ? $this->options['uc_buyTabletTime'] : '';//牌匾购买时间
      
        $db = new MySql();
        $user = new user($db);
		$userInfo = $user->getFullUserInfo($id);
	
        try{
            $db->beginTRAN();
			//检查是否已有联盟商家信息
			$sql = "SELECT * FROM `t_union_companyex` WHERE uc_uid='".$id."'";
			$uc = $db->getField($sql);				
			/* 如果没有联盟商家信息,那么新增 */
			if($uc == 0){
				$insert = array(
					'uc_uid'					 => $userInfo['u_id'], 
					'uc_ctime'					 => F::mytime(), 	
					'uc_shopInfo'				 => '',
					'uc_shopSpecial'			 => '',
					'uc_expressCompany'			 => '',
					'uc_expressNo'				 => '',
					'uc_validateContract'		 => 1,
					'uc_contractCode'			 => $uc_contractCode,
					'uc_validateContractTime'	 => $uc_validateContractTime,
					'uc_state'	  				 => 1,
					'uc_reject'					 => '',
					'uc_handleTime'				 => F::mytime(),
					'uc_isBuyTablet'			 => 1,
					'uc_tabletCode'			     => $uc_tabletCode,
					'uc_buyTabletTime'			 => $uc_buyTabletTime,
				);
				$update = [];
				$result = $db->insert('t_union_companyex', $insert);
				if(!$result){
					throw new Exception('-1');
				}				
			}
			/* 如果有联盟商家信息,那么修改 */
			else{
				$insert = [];
				$update = array(
					'uc_validateContract'		 => 1,
					'uc_contractCode'			 => $uc_contractCode,
					'uc_validateContractTime'	 => $uc_validateContractTime,
					'uc_state'	  				 => 1,
					'uc_reject'					 => '',
					'uc_handleTime'				 => F::mytime(),
					'uc_isBuyTablet'			 => 1,
					'uc_tabletCode'			     => $uc_tabletCode,
					'uc_buyTabletTime'			 => $uc_buyTabletTime,
				);
				$result = $db->update('t_union_companyex', $update, "uc_uid = '".$userInfo['u_id']."'");
				if(!$result){
					throw new Exception('-2');
				}				
			}
			/* 更新联盟商家标识 */
			/* substr('10111111111112', 0, 13) */
			$userUpdate = array(
				'u_auth'			 => substr($userInfo['u_auth'], 0, 13).'1',
				'u_companyName'		 => $u_companyName,
				'u_comArea'			 => $u_comArea,
				'u_comAddress'		 => $u_comAddress,
				'u_lng'				 => $u_lng,
				'u_lat'				 => $u_lat,
				'u_comLeadName'		 => $u_comLeadName,
				'u_indId'			 => $u_indId,
				'u_comMainIndustry'  => $u_comMainIndustry,
				'u_managerSelf'		 => $u_managerSelf,
				'u_isUnionSeller'	 => 1,
				'u_lastUpdateTime'	 => F::mytime()
			);
            $result = $db->update('t_user', $userUpdate, "u_id = '".$id."'");
            if ($result){				
                //记录操作日志
				$data = [];
                $data['memo'] = '激活/成为联盟商家 ';
                log::writeLogMongo(60131, 't_user', $id, array_merge($data, $userUpdate, $insert, $update));
                $db->commitTRAN();
                $this->show(message::getJsonMsgStruct('1001','修改成功'));
            }else{
                throw new Exception('-3');
            }
        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}
