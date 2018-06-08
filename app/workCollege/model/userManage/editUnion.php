<?php
/* 激活/成为联盟商家 */
class editUnion extends worker {
	
    private $db;
    function __construct($options) {       
        parent::__construct($options, [60131]);       
        $this->db = new MySql();
    }
    function run() {
		
		$id = isset($this->options['id']) ? trim($this->options['id']) : '';
		
		if(F::isEmpty($id)){
			exit;
		}
		
		$sql = "SELECT u_id, u_nick, u_isUnionSeller, u_companyName, u_indId, u_comMainIndustry, u_comArea, u_comAddress, u_comLeadName, u_managerSelf, u_auth, uc_id, 
				uc_validateContract, uc_validateContractTime, uc_contractCode, uc_isBuyTablet, uc_buyTabletTime, uc_tabletCode, u_lng, u_lat FROM `t_user` AS u
				LEFT JOIN
				t_union_companyex AS c
				ON u.u_id=c.uc_uid
				WHERE u_comArea=0 AND u_type=1 AND u_id='".$id."' AND u_level>2";//u_isUnionSeller=1 AND 
		$result = $this->db->getRow($sql);
		if(!$result){
			echo '<span class="font-red">等级不符合或者已有联盟商家所在区域(客户已经提交联盟商家申请或者异常数据)!</span>';
			exit;
		}
		$result['uc_validateContractTime'] = ($result['uc_validateContractTime'] == '0000-00-00 00:00:00' || F::isEmpty($result['uc_validateContractTime'])) ? F::mytime() : $result['uc_validateContractTime'];
		$result['uc_buyTabletTime'] = ($result['uc_buyTabletTime'] == '0000-00-00 00:00:00' || F::isEmpty($result['uc_buyTabletTime'])) ? F::mytime() : $result['uc_buyTabletTime'];
		//print_r($result);
		$data = array(
			'jsData' 		=> json_encode($result),
		    'code'          => 60131,
			'tempId'		=> 'temp_'.F::getGID()
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
