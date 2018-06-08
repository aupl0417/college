<?php
/**
 * 修改 特批标识
 * 提现可不用遵守银行卡户名是真实姓名/公司名/法人/经营者
 * 0：无特批；1：特批
 */
class editSpecialAllow_json extends worker {
	private $db;		
	function __construct($options) {		
        parent::__construct($options, [60129]);
		$this->db = new MySql();
    }

    function run() {
		$id = isset($this->options['id'])?$this->options['id']:"";
		$specialAllow = isset($this->options['specialAllow']) ? $this->options['specialAllow']: "";
		if ($id == ""){
			$this->show(message::getJsonMsgStruct('1002','参数错误'));
			exit;
		}
		$db = new MySql();
		$oldSpecialAllow = $db->getField("select u_specialAllow from t_user where u_id='".$id."'");
		$data = array(
			'u_specialAllow'  => $specialAllow,
		);
		try{
			$db->beginTRAN();
			$result = $db->update('t_user',$data, "u_id = '".$id."'");
			if(!$result){
				throw new Exception('1');
			}
			//记录操作日志
			$data['memo'] = '修改提现特批标识';
			$data['oldValue'] = $oldSpecialAllow;
			log::writeLogMongo(60129, 't_user', $id, $data);
			//历史操作
			$userTran = array(
				'ut_uid'	  => $id,
				'ut_type'	  => 27,
				'ut_eid'	  => $_SESSION['userID'],
				'ut_ctime'	  => F::mytime(),
				'ut_oldValue' => $oldSpecialAllow,
				'ut_newValue' => $specialAllow,
				'ut_reason'   =>$data['memo']
			);
			$resNk = $db->insert("t_user_tran", $userTran);
			if(!$resNk){
				throw new Exception('1');
			}
			$db->commitTRAN();
			$this->show(message::getJsonMsgStruct('1001', "修改成功！"));//成功

		}catch(Exception $e){
			$db->rollBackTRAN();
			$this->show(message::getJsonMsgStruct('1002','操作失败!'));
		}
	}
	
}
