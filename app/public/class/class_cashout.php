<?php

/**
 *
 * 账户提现
 *
 * @author adadsa
 *
 * @time 2016-04-27
 *

 */
class cashout extends account{

    private $error;
	/* 可提现账户类型 */
	private $accounts = [
		'1' => 'ac_freeMoney',//现金账户
		'2' => 'ac_busMoney',//交易账户
		'3' => 'ac_misMoney',//佣金账户
	];
    public function __construct($db = NULL) {
        if(!$this->db){
            parent::__construct(is_null($db) ? new MySql() : $db,F::mytime());
        }
    }

 	/* 提现
	*$uid 会员id
	*$accountType 账户类型 1-现金账户;2-交易账户;3-佣金账户
	*$password 支付密码
	*$account 提现账户
	*$card 提现类型 1-银行卡; 0-支付宝;
	*$money 提现金额
	*return ca_id or false;
	*/
	public function add($uid, $password, $accountType, $account, $card = 1, $money){
		$systemParas = attrib::getSystemParas();//系统参数		
		$cash_out_free_tax_rat = $systemParas['cash_out_free_tax_rat'];//现金账户提现手续费
		$cash_out_bus_tax_rat = $systemParas['cash_out_bus_tax_rat'];//货款提现手续费
		$cash_out_mis_tax_rat = $systemParas['cash_out_mis_tax_rat'];//佣金提现手续费
		$cash_out_min_money = $systemParas['cash_out_min_money'];//单次提现最小金额(元)
		$cash_out_max_money = $systemParas['cash_out_max_money'];//单次提现最大金额(元)
		$cash_out_max_time = $systemParas['cash_out_max_time'];//每天最多提现次数
		
		$accounts = [
		];
		/* 交易类型 */
		$bsTypes = [
			'1' => '20201',
			'2' => '20202',
			'3' => '20203'
		];
		/* 服务费比例 */
		$taxRats = [
			'1' => $cash_out_free_tax_rat,
			'2' => $cash_out_bus_tax_rat,
			'3' => $cash_out_mis_tax_rat
		];
		/* 服务费类型 */
		$taxTypes = [
			'1' => '23301',
			'2' => '23302',
			'3' => '23303'
		];
		/* 校验提现账户类型是否正确 */
		if(!array_key_exists($accountType, $accounts)){//账户类型不正确
            
		}		
		
	}

    public function getError(){
        return $this->error;
    }
}
