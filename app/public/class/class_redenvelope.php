<?php

/* ClassName: redenvelope
 * Memo:红包

 * 
 * */

class redenvelope {
    private $db;
	private $error;
	/* 抢红包的参数 */
	private $redid;//红包id
	private $rede;//红包信息
	private $maxConcurrent = 1;//单个红包最大并发

    public function __construct($db = NULL) {
        $this->db = is_null($db) ? new MySql() : $db;       
    }
	
	public function setMax($value = 200){
		$this->maxConcurrent = $value;
	}
	
	//添加红包 $data:红包
	public function add($data){
		return $this->db->insert('t_red_envelope', $data) == 1;
	}
	
	//取消红包 $id
	public function cancel($id){
		return $this->db->update('t_red_envelope', ['red_state' => 0], "red_id = '".$id."'");
	}
	
	//到时间的红包设置为过期/完成 $id
	public function setEnd($id = ''){
		$db = new MySql();
		//过期
		$where = " UNIX_TIMESTAMP(red_endTime) < ".time()." AND red_leftNumber > 0 ";
		if($id){
			$where .= " AND red_id = '".$id."'";
		}
		//echo $where;
		$result1 = $db->update('t_red_envelope', ['red_state' => -1], $where);
		
		//echo $result1 . ' - ssss';die;
		//已发完
		$where = " red_leftNumber = 0 ";
		if($id){
			$where .= " AND red_id = '".$id."'";
		}		
		$result2 = $db->update('t_red_envelope', ['red_state' => 2], $where);
		
		return $result1 + $result2;
	}
	
	//读取红包列表 $state:状态 0- 取消;1-有效红包;-1 - 过期   $limit:数量
	public function getList($state = 1, $limit = 10){
		$sql = "select * from t_red_envelope where";
		switch($state){
			case 1:
				$sql .= " red_state = 1 and UNIX_TIMESTAMP(red_startTime) < ".time()." and red_leftNumber > 0";
				break;
			case 0:
				
				break;
			case 2:
				
				break;
			case -1:
			
				break;
			case 8://摇一摇
				$sql = " red_state = 1 and UNIX_TIMESTAMP(red_startTime) < ".time()." and red_type=1";
				break;
			case 9:	
			default: 
				$sql .= " red_state in (1,2) and red_type = 0";
				break;
		}
		
		$sql .= " order by red_state asc, red_startTime asc, red_createTime desc limit 0, ".$limit;
		return $this->db->getAll($sql);
	}
	
	//读取红包信息 $id:红包id
	public function get($id, $lock = false){
		$sql = "select * from t_red_envelope where red_id = '".$id."'";
		if($lock){
			$sql .= " FOR UPDATE";
		}
		
		$this->rede = $this->db->getRow($sql);
		if($this->rede){
			return $this->rede;
		}else{
			$this->error = '红包不存在';
			return false;
		}
	}
	
	//更新红包信息 $id:红包id $data更新数据
	public function update($id, $data){
		
	}
	
	//抢红包
	public function lucky($id,$userId=0){	
		if($userId === 0){
		//检验是否已经领过这个红包了,如果领过,不能重复领取
 		$hasLucky = $this->db->getRow("select * from t_red_envelope_tran where rt_redid = '".$id."' and rt_to = '".$_SESSION['userID']."'");
		}else{
			$hasLucky = $this->db->getRow("select * from t_red_envelope_tran where rt_redid = '".$id."' and rt_to = '".$userId."'");
		}
		if($hasLucky){
			$hasLucky['isGetRed'] = 1;
			$this->error = '已领取红包';
			return $hasLucky;
		} 
		$cache = new cache();
		$maxCon = $cache->get($id);
		if($maxCon){
			if($maxCon >= $this->maxConcurrent){//超过最大并发
				$this->error = '9999';//超过最大并发
				return false;
			}else{
				$cache->inc($id);
			}
		}else{
			$cache->set($id, 1, 12);
		}		
		
		$this->get($id, true);//获取红包信息		
		
		if($this->rede['red_state'] != '1'){//不是正在发放的红包
			$this->error = '无效的红包';
			return false;
		}
		
		if($this->rede['red_leftNumber'] <= 0 || $this->rede['red_leftMoney'] <= 0){//剩余个数或金额不足
			$this->setEnd($id);
			$this->error = '红包已抢完';
			return false;
		}
		
		if(strtotime($this->rede['red_startTime']) > time()){//未开始
			$this->error = '无效的红包';
			return false;
		}
		
		if(strtotime($this->rede['red_endTime']) < time()){//已过期
			$this->setEnd($id);
			$this->error = '无效的红包';
			return false;
		}
		
		$randMoney = $this->getRandMoney();
		if($randMoney){
			$money = $randMoney['money'];
			$type = $randMoney['type'];
		}else{	
			$this->error = '系统错误';//金额错误
			return false;			
		}
		if($userId === 0){
		    $userId = $_SESSION['userID'];
    		$username = isset($_SESSION['userNick']) ? $_SESSION['userNick'] : '临时用户';
		}else{
    		$username = $this->db->getField("select u_nick from t_user where u_id='".$userId."'");
		}
		//插入红包明细表
		$r_id = F::getTimeMarkID();
		$detail = [
			'rt_id'			 => $r_id,
			'rt_redid'		 => $id,
			'rt_from'		 => $this->rede['red_uid'],
			'rt_to'			 => $userId,//F::getTimeMarkID(),//
			'rt_toNick'		 => $username,
			'rt_money'		 => $money,
			'rt_moneyType'	 => $type,
			'rt_time'		 => F::mytime(),
			'rt_state'		 => 0,
		];
		$result = $this->db->insert('t_red_envelope_tran', $detail);
		if($result < 1){
			$this->error = '系统错误';//插入红包明细表失败
			return false;
		}
		$money = ($type > 1) ? bcdiv($money, 100, 2) : $money;
		$leftMoney = bcsub($this->rede['red_leftMoney'], $money, 2);
		$leftNumber = $this->rede['red_leftNumber'] - 1;
		$data = [
			'red_leftMoney'  => $leftMoney,
			'red_leftNumber' => $leftNumber,
		];
		/* 如果是最后一个红包,那么设置为已完成 */
		if($leftNumber == 0){
			$data['red_state'] = 2;
			$data['red_endAtTime'] = F::mytime();
		}	
		$result = $this->db->update('t_red_envelope', $data, " red_id = '".$id."'");
		if($result < 1){
			$this->error = '系统错误';//更新红包信息失败
			return false;			
		}
		
		//领完红包,减数
		$cache->dec($id);
		
		$detail['num'] = $cache->get($id);
		$detail['isGetRed'] = 0;
		return $detail;
	}
	
	//计算本次领取金额
	public function getRandMoney(){
		$leftNumber = $this->rede['red_leftNumber'];//剩余数量
		$leftMoney = $this->rede['red_leftMoney'];//剩余金额
		if($leftNumber == 1){//如果只剩最后一个红包,那么剩余的钱都给他
			$randMoney = $leftMoney;
		}else{
			$baseMoney = 0.01;//保证最少能够得到一分钱
			
			$randMax = bcdiv(($leftMoney - $leftNumber * $baseMoney) * 100, $leftNumber, 0) * 2 ;//随机安全上限/分
			$randMoney = $baseMoney + (mt_rand(0, $randMax)) / 100;
		}
		
		$randCash = ($this->rede['red_cashRatio'] > 0) ? array_fill(0, $this->rede['red_cashRatio'], 1) : [];
		$randRed = ($this->rede['red_redRatio'] > 0) ? array_fill(0, $this->rede['red_redRatio'], 2) : [];
		$randWhite = ($this->rede['red_whiteRatio'] > 0) ? array_fill(0, $this->rede['red_whiteRatio'], 3) : [];
		$randArray = array_merge($randCash, $randRed, $randWhite);
		if(count($randArray) != 100){
			return false;			
		}
		$r = mt_rand(0, 99);
		$moneyType = $randArray[$r];
		$randMoney = ($moneyType > 1) ? F::bankerAlgorithm($randMoney, 100, 2) : $randMoney;//如果是积分,乘100倍
		//$arr = [1,1,1,1,1,1,1,2,2,2,2,2,2,2,3,3,3,3,3,3,3];
		//$idx = mt_rand(0,99);
		//$arr[$idx];
		return [
			'money' => $randMoney,
			'type'  => $moneyType
		];
	}
	
	//红包余额转到账户
	//红包类型:$type  1-现金账户; 2-红积分; 3-白积分
	public function red2Account($type = 1){		
		$ac = new account($this->db);
	}
	
	//生成红包链接
	public function redlink($id){
		//return 
	}
	
	

	//返回错误
    public function getError() {
        return $this->error;
    }	
}

?>
