<?php

class indexNew_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [60110]);
    }

    function run() {	
        $db = new MySql();
		$dataTable = new datatableHuge();
		$emptyData = json_encode(array(
			'draw' => 0,
			'recordsTotal' => 0,
			'recordsFiltered' => 0,
			'data' => [],			
		));	
		$type = isset($this->options['type']) ? (F::fmtNum($this->options['type']) - 0) : 0;
		
        $columns = array(
            array(
                'db' => 'u.u_code',
                'dt' => 'DT_RowId',
                'formatter' => function( $d, $row ) {
                    return 'row_' . $d;
                }
            ),           
            array('db' => 'u.u_id',	 		     'dt' => 'id'),
            array('db' => 'u.u_type',			 'dt' => 'type'),
            array('db' => 'u.u_nick',			 'dt' => 'nick'),
            array('db' => 'u.u_name',			 'dt' => 'name'),
            array('db' => 'u.u_sex', 			 'dt' => 'sex'),
            array('db' => 'u.u_certNum',	 	 'dt' => 'certNum'),
            array('db' => 'u.u_birth',			 'dt' => 'birth'),
            array('db' => 'u.u_email',			 'dt' => 'email'),
            array('db' => 'u.u_tel',			 'dt' => 'mobile'),
            array('db' => 'u.u_qq',				 'dt' => 'qq'),
            array('db' => 'u.u_certNum',		 'dt' => 'certNum'),
            array('db' => 'u.u_area',			 'dt' => 'area'),
            array('db' => 'u.u_indId',			 'dt' => 'indId'),
            array('db' => 'u.u_address',		 'dt' => 'address'),
            array('db' => 'u.u_level',			 'dt' => 'level'),
            array('db' => 'u.u_code',			 'dt' => 'code'),
            array('db' => 'u.u_fCode',			 'dt' => 'fCode'),
            array('db' => 'u.u_companyName',	 'dt' => 'companyName'),
            array('db' => 'u.u_companyType',  	 'dt' => 'companyType'),
            array('db' => 'u.u_comArea',	  	 'dt' => 'comArea'),
            array('db' => 'u.u_comLicenseCode',	 'dt' => 'licenseCode'),
            array('db' => 'u.u_state',			 'dt' => 'state'),
            array('db' => 'u.u_auth',			 'dt' => 'auth'),
            array('db' => 'u.u_isUnionSeller',	 'dt' => 'isUnion'),
            array('db' => 'u.u_comLegalName', 	 'dt' => 'legalName'),
            array('db' => 'u.u_createTime', 	 'dt' => 'cTime'),
            //array('db' => 'f.u_nick', 			 'dt' => 'fNick'),
            //array('db' => 'f.u_type', 			 'dt' => 'fType'),
            //array('db' => 'f.u_companyName', 	 'dt' => 'fCompanyName'),
            //array('db' => 'ul.ul_name',		 	 'dt' => 'levelName'),
            array('db' => 'u.u_upgrade',		 'dt' => 'upgrade'),
            array('db' => 'u.u_isQuit',			 'dt' => 'quit'),
            array('db' => 'u.u_logout',			 'dt' => 'logout'),
            array('db' => 'u.u_upgrade',		 'dt' => 'upgrade'),
            array('db' => 'u.u_upgradeTime',	 'dt' => 'upgradeTime'),
			
        );

		$where = '';
		//银行卡
		if(isset($this->options['search']) && isset($this->options['search']['bank'])){
			$where .= " AND EXISTS (SELECT 1 FROM t_accountex WHERE aex_account='".$this->options['search']['bank']['value']."' AND aex_type=0 AND aex_uid=u.u_id)";
			unset($this->options['search']['bank']);
		}
		//信用卡
		if(isset($this->options['search']) && isset($this->options['search']['credit'])){
			$where .= " AND EXISTS (SELECT 1 FROM t_accountex WHERE aex_account='".$this->options['search']['credit']['value']."' AND aex_type=1 AND aex_uid=u.u_id)";
			unset($this->options['search']['credit']);
		}
		//支付宝
		if(isset($this->options['search']) && isset($this->options['search']['alipay'])){
			$where .= " AND EXISTS (SELECT 1 FROM t_accountex WHERE aex_account='".$this->options['search']['alipay']['value']."' AND aex_type=2 AND aex_uid=u.u_id)";
			unset($this->options['search']['alipay']);
		}
		//推荐人
		if(isset($this->options['search']) && isset($this->options['search']['fcode'])){
			$sql = "select u_code from t_user where u_nick='".$this->options['search']['fcode']['value']."'";
			$fCode = $db->getField($sql);
			if(!$fCode){
				echo $emptyData;exit;
			}else{
				$where .= " and u_fCode='".$fCode."'";
				
			}
			
			//$where .= " AND EXISTS (SELECT 1 FROM t_accountex WHERE aex_account='".$this->options['search']['alipay']['value']."' AND aex_type=2 AND aex_uid=u.u_id)";
			unset($this->options['search']['fcode']);
		}
		//联盟商家
		if(isset($this->options['search']) && isset($this->options['search']['isUnion']) && isset($this->options['search']['isUnion']['value'])){
			if($this->options['search']['isUnion']['value'] == 2){
				$this->options['search']['isUnion']['value'] = 1;
				$this->options['search']['comArea'] = [
					'value' => 0,
                    'filter' => 'eq',
                    'num' => 0
				];
			}
			
			
		}
		
		
		//print_r($this->options);
		
/* 		$sql = "SELECT ### FROM
				`t_user` as ul
				LEFT JOIN 
				t_user_level AS ul
				ON u.u_level = ul.ul_id
				where u_type = '".$type."' "; */
		$sql = "SELECT ### FROM t_user AS u WHERE u.u_code > 0 and u.u_type = '".$type."'".$where;
		//echo $sql;		
        $result = $dataTable->create($this->options, $sql, $columns);
		//print_r($result);
		if($result['data']){
			
			$levels = $db->getAll("select ul_id, ul_name from t_user_level");
			$levels = array_column($levels, 'ul_name', 'ul_id');
			$fCodes = F::addYh(implode('|', array_unique(array_column($result['data'], 'fCode'), SORT_REGULAR)));
			//print_r($fCodes);
			$sql = "select u_companyName, u_nick, u_type, u_level,u_code from t_user where u_code in (".$fCodes.")";
			$fUsers = $db->getAll($sql);
			
			if($fUsers){
				$fCompanyNames	 = array_column($fUsers, 'u_companyName', 'u_code');
				$fNicks			 = array_column($fUsers, 'u_nick', 'u_code');
				$fTypes			 = array_column($fUsers, 'u_type', 'u_code');
				$fLevels		 = array_column($fUsers, 'u_level', 'u_code');
			}else{
				$fCompanyNames = $fNicks = $fTypes = $fLevels = array();			
			}
			
			foreach ($result['data'] as $k => $v){
				$result['data'][$k]['levelName']		 = array_key_exists($v['level'], $levels) ? $levels[$v['level']] : ' - ';
				$fLevel									 = array_key_exists($v['fCode'], $fLevels) ? $fLevels[$v['fCode']] : ' - ';
				$fLevel									 = array_key_exists($fLevel, $levels) ? $levels[$fLevel] : ' - ';
				$result['data'][$k]['fNick']			 = array_key_exists($v['fCode'], $fNicks) ? $fNicks[$v['fCode']].'<br/>'.$fLevel : ' - ';
				$result['data'][$k]['fType']			 = array_key_exists($v['fCode'], $fTypes) ? $fTypes[$v['fCode']] : ' - ';
				$result['data'][$k]['fCompanyName']		 = array_key_exists($v['fCode'], $fCompanyNames) ? $fCompanyNames[$v['fCode']] : ' - ';
				
			}			
		}
        echo json_encode($result);		
	}
}
