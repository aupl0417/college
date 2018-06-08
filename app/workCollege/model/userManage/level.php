<?php
/* 等级调整,只能往上不能往下 */
class level extends worker {	
	private $db;		
	function __construct($options) {		
        parent::__construct($options, [60106]);
		$this->db = new MySql();
    }

    function run() {
		if(!isset($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;			
		}
		$userLevel = $this->db->getAll("select ul_id, ul_name from t_user_level order by ul_id asc");
		$userLevel = array_column($userLevel, 'ul_name', 'ul_id');
        $user = new user();
		$userInfo = $user->getUserByID($this->options['id'], 'u_level, u_fCode, u_code, u_type');
		
		$info = array(
			'level'		=> $userInfo['u_level'],
			'fCode'	 	=> $userInfo['u_fCode'],
			'code'		=> $userInfo['u_code'],
			'type'		=> $userInfo['u_type'],
			'userLevel' => F::array2Options($userLevel),	
			'id'		=> $this->options['id'],
		);
		
		$this->setReplaceData($info);
		
        $this->setTempAndData();
        $this->show();
	}
}
