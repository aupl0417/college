<?php
/**
 * 代理公司落地
 * 计算代理公司奖励
 * @author jojojing
 */
class agent
{
    private $db = null;
    private $error;
    private $account;

    public function __construct($db = NULL, $account = Null)
    {
        $this->db = is_null($db) ? new MySql() : $db;
        $this->account = is_null($account) ? new account($this->db) : $account;
    }

    /* 参数： 公司（必填）；uid（选填） 计算代理公司奖励  */
    public function agentFullBack($comId, $uid = '')
    {
        if (!isset($comId)) {
            $this->error = -1;  //代理公司账号不存在
            return false;
        }

        if (!F::isEmpty($uid)) {
            $sql = "select cgl_orderId,cgl_name,cgl_discount,cgl_uid from t_company_gplp where cgl_uid = '" . $uid . "' and cgl_comId = '" . $comId . "'";
            $result = $this->db->getField($sql);
            if (!$result) {
                $this->error = -8;  //当前用户没有购买代理
                return false;
            }

            $sql = "select bu_money from t_order where bu_id = '" . $result['cgl_orderId'] . "'";
            $price = $this->db->getField($sql);    //购买gp/lp价格
            if (!$price) {
                $this->error = -7;  //不存在订单价格
                return false;
            }

            $sql = "select * from t_account_cash_tran where ca_businessId = '222' and ca_to = '" . $comId . "' and ca_source = '" . $uid . "'";  //判断是否重复计算奖励
            if ($this->db->getRow($sql)) {
                $this->error = -2;  //已计算代理奖励
                return false;
            }
            return $this->agentTransfer($price, $comId, $result['cgl_orderId'], $uid, $result['cgl_name']); /* 计算代理公司奖励 */

        } else {
            $sql = "select cgl_orderId,cgl_name,cgl_discount,cgl_uid from t_company_gplp where cgl_comId = '" . $comId . "'";
            $comFullBack = $this->db->getAll($sql);
            if (!$comFullBack) {
                $this->error = -3;  //没有购买该代理公司的代理
                return false;
            }

            foreach ($comFullBack as $k => $v) {
                $sql = "select bu_money from t_order where bu_id = '" . $v['cgl_orderId'] . "'";
                $price = $this->db->getField($sql);    /* 购买gp/lp价格 */
                if (!$price) {
                    $this->error = -7; //不存在订单价格
                    return false;
                }

                $sql = "select * from t_account_cash_tran where ca_businessId = '222' and ca_to = '" . $comId . "' and ca_source = '" . $v['cgl_uid'] . "'";  //判断是否重复计算奖励
                if ($this->db->getRow($sql)) {
                    $this->error = -2; //已计算代理奖励
                    return false;
                }
                if (!$this->agentTransfer($price, $comId, $v['cgl_orderId'], $v['cgl_uid'], $v['cgl_name'])) {  /* 计算代理公司奖励 */
                    $this->error = -5;  //计算提成失败
                    return false;
                }
            }
            return true;
        }
    }

    /* 获取对应代理（奖励\提成）参数 */
    private function getParams($id)
    {
        $cache = new cache();
        $cacheId = 'ri_L1';
        $paramData = $cache->get($cacheId);
        if (empty($paramData)) {
            $sql = "select ri_id,ri_L1 from t_company_reward_rule";
            $paramData = $this->db->getAll($sql);
            $paramData = array_column($paramData, 'ri_L1', 'ri_id');
            if (!$paramData[$id] || $paramData[$id] == 0.00) {
                $this->error = -4;  //获取参数错误
                return false;
            }
            $cache->set($cacheId, $paramData, 2);
        }
        return $paramData[$id];
    }

    /* 计算--代理费金额全返积分奖励101%，所销售代理金额的奖励积分10%，所销售代理权金额的奖励现金10%  */
    public function agentTransfer($price, $comId, $orderId = '', $uid, $uNick)
    {
        $arr = array(
            '0' => 2,
            '1' => 4,
        );
        $fullBack = F::bankerAlgorithm($price*100, $this->getParams($arr[0]), 4);/* 标准代理费金额全返积分奖励 101% */
        $salReScore = F::bankerAlgorithm($price*100, explode('|', $this->getParams($arr[1]))[0], 4);/* 所销售代理金额的奖励积分 10% */
        $salReCash = F::bankerAlgorithm($price, explode('|', $this->getParams($arr[1]))[1]);/* 所销售代理权金额的奖励现金 10% */

        if (!$this->account->transferScore('125', ADMIN_ID, 5, $comId, 5, $fullBack * 100, $_SESSION['userID'], 0, 2, $orderId, '代理费金额全返积分奖励101%', $uid, $uNick)) {
            $this->error = -5;  //账户异常
            return false;
        }
        if (!$this->account->transferScore('126', ADMIN_ID, 5, $comId, 5, $salReScore * 100, $_SESSION['userID'], 0, 2, $orderId, '所销售代理权金额的奖励积分10%', $uid, $uNick)) {
            $this->error = -5;  //账户异常
            return false;
        }
        if (!$this->account->transferCash('222', ADMIN_ID, 3, $comId, 3, $salReCash, $_SESSION['userID'], 0, 2, $orderId, '所销售代理权金额的奖励创业金10%', $uid, $uNick)) {
            $this->error = -5;  //账户异常
            return false;
        }
        $arr = array($price, $comId, $fullBack, $salReScore, $salReCash, $uid, $uNick, '订单' . $orderId);
        log::writeLogMongo('6070101', 't_account', $comId, $arr);  //写入日志
        return true;
    }


    /**
     * 代理会员购买成功白积分或奖金反返
     * @param $orderId
     * @param $type 1.代理会员白积分反返 2.促成人白积分和现金奖励 3.代理和促成人奖励一起计算
     * @return bool
     */
    public function agentPersonFullBack($orderId, $type=1)
    {
        if (!isset($orderId)) {
            $this->error = -7;  /*代理订单不存在*/
            return false;
        }
        $sql = "SELECT cgl_id,cgl_uid,cgl_facilitate,cgl_name FROM t_company_gplp WHERE cgl_orderId = '" . $orderId . "'";
        $result = $this->db->getRow($sql);
        if (!$result) {
            $this->error = -3;  /*该没有购买代理*/
            return false;
        }
        $sql = "SELECT bu_money FROM t_order WHERE bu_id = '" . $orderId . "'";
        $price = $this->db->getField($sql);
        if (!$price) {
            $this->error = -7;  /*不存在订单价格*/
            return false;
        }
        switch ($type) {
            case 1:
                /*检测是否已经返还白积分*/
                if(!$this->checkScore($orderId,$result['cgl_uid'])){
                    $this->error = -9;  /*该订单已经返还白积分*/
                    return false;
                }
                break;
            case 2:
                /*检测是否已经返还白积分*/
                if(!$this->checkScore($orderId,$result['cgl_facilitate'])){
                    $this->error = -10;  /*该订单已经返还白积分*/
                    return false;
                }
                /*检测已经返还提成奖励*/
                if(!$this->checkCash($orderId,$result['cgl_facilitate'])){
                    $this->error = -11;  /*该订单已经返还提成奖励*/
                    return false;
                }
                break;
            case 3:
                /*检测是否已经返还白积分*/
                if(!$this->checkScore($orderId,$result['cgl_uid'])){
                    $this->error = -9;  /*该订单已经返还白积分*/
                    return false;
                }
                /*检测是否已经返还白积分*/
                if(!$this->checkScore($orderId,$result['cgl_facilitate'])){
                    $this->error = -10;  /*该订单已经返还白积分*/
                    return false;
                }
                /*检测已经返还提成奖励*/
                if(!$this->checkCash($orderId,$result['cgl_facilitate'])){
                    $this->error = -11;  /*该订单已经返还提成奖励*/
                    return false;
                }
                break;
        }
        /*积分或提成返还*/
        if(!$this->PersonTransfer($price,$orderId,$result['cgl_uid'],$result['cgl_facilitate'],$type))
        {
           // $this->error = -6;  //计算提成失败
            return false;
        }
        return true;
    }

    /**
     * @param $price 订单价格 必传
     * @param $orderId 订单ID 必传
     * @param $uid 代理会员 必传
     * @param $facilitate 促成人 必传
     * @param $type 返还类型 必传 1.代理会员白积分反返 2.促成人白积分和现金奖励 3.代理和促成人奖励一起计算
     * @return bool
     */
    public function PersonTransfer($price,$orderId,$uid,$facilitate,$type){
        $arr = array(
            '0' => 1,
            '1' => 3,
        );
        /* 标准代理费金额全返积分奖励 101% */
        $meScore = F::bankerAlgorithm($price*100, $this->getParams($arr[0]), 4);
        /* 代理促成人金额的奖励积分 10% */
        $facilitateScore = F::bankerAlgorithm($price*100,explode('|', $this->getParams($arr[1]))[0], 4);
        /*代理促成人权金额的奖励现金 10% */
        $facilitateCash = F::bankerAlgorithm($price,explode('|',$this->getParams($arr[1]))[0]);
        switch($type){
            case 1:
                /*返还代理会员白积分*/
                if(!$this->account->transferScore(125, ADMIN_ID, 5,$uid, 5, $meScore, $_SESSION['userID'], 0, 2,$orderId, '代理会员销售白积分奖励--奖励比例为101%'))
                {
                    $this->error = -5;  //账户异常
                    return false;
                }
                break;
            case 2:
                /*返还代理促成人白积分*/
                if(!$this->account->transferScore(125, ADMIN_ID, 5,$facilitate, 5, $facilitateScore, $_SESSION['userID'], 0, 2,$orderId, '代理会员销售白积分奖励--奖励比例为10%'))
                {
                    $this->error = -5;  //账户异常
                    return false;
                }
                /*返还代理促成人现金奖励*/
                if(!$this->account->transferCash(221, ADMIN_ID, 3,$facilitate, 3, $facilitateCash, $_SESSION['userID'], 0, 2,$orderId, '代理会员销售现金奖励--奖励比例为10%'))
                {
                    $this->error = -5;  //账户异常
                    return false;
                }
                break;
            case 3:
                /*返还代理会员白积分*/
                if(!$this->account->transferScore(125, ADMIN_ID, 5,$uid, 5, $meScore, $_SESSION['userID'], 0, 2,$orderId, '代理会员销售白积分奖励--奖励比例为101%'))
                {
                    $this->error = -5;  //账户异常计算提成失败
                    return false;
                }
                /*返还代理促成人白积分*/
                if(!$this->account->transferScore(125,ADMIN_ID,5,$facilitate,5,$facilitateScore, $_SESSION['userID'], 0, 2,$orderId, '代理会员销售白积分奖励--奖励比例为10%'))
                {
                    $this->error = -7;  //账户异常
                    return false;
                }
                /*返还代理促成人现金奖励*/
                if(!$this->account->transferCash(221, ADMIN_ID, 3,$facilitate,3,$facilitateCash, $_SESSION['userID'], 0, 2,$orderId, '代理会员销售现金奖励--奖励比例为10%'))
                {
                    $this->error = -5;  //账户异常
                    return false;
                }
                break;
        }
        /*记录异动日志*/
        $arr = array($price, $uid, $meScore, $facilitateScore, $facilitateCash, $uid, $facilitate, '订单' . $orderId);
        log::writeLogMongo('3060207', 't_account', $uid, $arr);  //写入日志
        return true;

    }


    /**
     * 检测订单都否已返还白积分
     * @param $orderId 订单号
     * @param $sc_to 返送账户
     * @return bool
     */
    public function checkScore($orderId,$sc_to){
        /*判断该订单是否已经返还了白积分*/
        $sql = "SELECT sc_id FROM `t_account_score_tran` WHERE sc_toFlag = '5' AND sc_businessId='125' AND sc_orderId='".$orderId."' AND sc_to='".$sc_to."'";
        $score = $this->db->getField($sql);
        return !empty($score) ? false : true;
    }

    /**
     * 检测订单是否已返还现金奖励
     * @param $orderId
     * @param $sc_to
     * @return bool
     */
    public function checkCash($orderId,$sc_to){
        /*判断该订单是否已经返还了白积分*/
        $sql = "SELECT ca_id FROM `t_account_cash_tran` WHERE ca_toFlag = '3' AND ca_businessId='221' AND ca_orderId='".$orderId."' AND ca_to='".$sc_to."'";
        $cash = $this->db->getField($sql);
        return !empty($cash) ? false : true;
    }

    /* 返回错误信息 */
    public function getError()
    {
        $msgErr = [
            '-1' => '代理公司账号不存在',
            '-2' => '已计算代理奖励',
            '-3' => '没有购买代理公司的代理该',
            '-4' => '获取参数错误',
            '-5' => '账户异常',
            '-6' => '计算提成失败',
            '-7' => '不存在订单价格',
            '-8' => '当前用户没有购买代理',
            '-9'=>'该订单代理会员的白积分已返还',
            '-10'=>'该订单代理促成人的白积分已返还',
            '-11'=>'该订单代理促成人的现金奖励已返还'
        ];
        $this->error = array_key_exists($this->error, $msgErr) ? $msgErr[$this->error] : $this->error;
        return $this->error;
    }

}