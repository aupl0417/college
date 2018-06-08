<?php
/*
 * 工单类
 * adadsa 2015-03-28
 */

class flow {
    private $db;
    private $error;
    private $flowId;
    private $flowInfo;//流程信息
    private $flowJson;//流程Json
    public $flowTable;//工单表


    public function __construct($id, $db = null) {
        $this->flowId = $id;
        $this->db = is_null($db) ? new MySql() : $db;
        $this->_getFlow();
    }

    /* 取得流程所有操作 */
    public function getActionList(){
        $sql = "SELECT * FROM t_flow_action WHERE fa_flowid = '".$this->flowId."' order by fa_showOrder asc";
        return $this->db->getAll($sql);
    }

    //获取顶级工单的信息
    private function getParentFlowInfo($fields='*'){
        $sql = "SELECT $fields FROM t_flow WHERE flow_id=".$this->flowId;
        return $this->db->getRow($sql);
    }

    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  获取当前工单信息
        * @Param $fid   工单id
        * @Returns array
        * author wyh
     */
    /* ----------------------------------------------------------------------------*/
    private function getCurrentFlowInfo($fid){
        if (empty($fid)) {
            return false;
        }

        $flowInfo   = $this->getParentFlowInfo('flow_table');
        $table      = $flowInfo['flow_table'];
        $tableIndex = current($this->db->tableIndex($table));
        $sql        = "SELECT * FROM $table WHERE $tableIndex = $fid";
        return $this->db->getRow($sql);
    }

    /**
     * 通过流程ID获取流程名称
     * @param $faId
     * @return array
     */
    public function getAction($faId){
        if(empty($faId)){
            return array();
        }
        $db = new MySql();
        $sql = "SELECT fa_name FROM t_flow_action WHERE fa_id = '".$faId."'";
        $result = $db->getRow($sql);
        return !empty($result) ? $result['fa_name'] : array();

    }

    /* 取得流程的json数据 */
    public function getFlowJson(){
        return F::TextToHtml($this->flowInfo['flow_json']);
    }

    /* 格式化json数据 */
    private function _fmtFlowJson(){
        return json_decode(F::TextToHtml($this->flowInfo['flow_json']));
    }

    /* 取得流程信息 */
    private function _getFlow(){
        $result = $this->db->getRow("select * from t_flow where flow_id='".$this->flowId."'");
        if($result){
            $this->flowInfo  = $result;
            $this->flowTable = $result['flow_table'];
            $this->flowJson  = $this->_fmtFlowJson();
            return $result;
        }else{
            $this->error = '流程不存在';
            return false;
        }
    }

    /* 返回节点所处数组键名 */
    private function _getStepIdx($step){
        $key = false;
        foreach($this->flowJson->nodeDataArray as $k => $node){
            if(isset($node->step) && $node->step == $step){
                return $k;
            }
        };
        return $key;
    }

    /*返回某步骤可指派的部门
     * author wyh
     */
    public function getPowerOrg($step){
        $idx = $this->_getStepIdx($step);
        if ($this->flowJson->nodeDataArray[$idx]->power->orgs !== null) {
            return $this->flowJson->nodeDataArray[$idx]->power->orgs;
        }else{
            $this->error = 401;
            return false;
        }
    }

    /*返回某步骤可指派的员工
     * author wyh
     */
    public function getPowerWorker($step){
        $idx = $this->_getStepIdx($step);
        if (!empty($this->flowJson->nodeDataArray[$idx]->power->workers)) {
            return array_unique($this->flowJson->nodeDataArray[$idx]->power->workers);
        }else{
            $this->error = 402;
            return false;
        }
    }


    /* 判断当前操作是否有权限
        如果当前操作指定了员工,那么检查当前操作的员工是否符合
        当前部门具有特殊操作权限的用户可以执行特殊操作:分配任务(没有指定员工的情况);重新指派/撤销(已经指派员工的情况)
        @param fid 工单id
        @param eid 操作人
     */
    public function checkPower($step,$eid=0,$fid=0){
        $idx = $this->_getStepIdx($step);
        //指派了员工
        $eid = empty($eid) ? $_SESSION['userID'] : $eid;
        //返回各操作权限
        $power = array(
            'power'       => false,
            'powerAssign' => false,
            'powerCancel' => false,
        );
        foreach ($power as $k=>$v) {
            //操作人
            if (isset($this->flowJson->nodeDataArray[$idx]->$k->workers) && $this->flowJson->nodeDataArray[$idx]->$k->workers !== null) {
                $power[$k] = in_array($eid,$this->flowJson->nodeDataArray[$idx]->$k->workers);
            }else{
                //指派了部门
                /* 取出员工所属部门,及其所有下属部门 */
                $departmentID = $this->db->getField("SELECT e_departmentID FROM t_employee WHERE e_id='$eid'");
                $sql = "SELECT org2.dm_id FROM `t_organization` AS org1 LEFT JOIN `t_organization` AS org2 ON org2.dm_code LIKE CONCAT(org1.dm_code,'%') WHERE org1.dm_id=$departmentID";
                $orgs = $this->db->getAll($sql);
                if(isset($this->flowJson->nodeDataArray[$idx]->$k->orgs) && $orgs && $this->flowJson->nodeDataArray[$idx]->$k->orgs !== null){
                    $orgs      = array_column($orgs, 'dm_id');
                    $power[$k] = count(array_intersect($this->flowJson->nodeDataArray[$idx]->$k->orgs, $orgs));
                }else{
                    $this->error = '-101';
                }
            }
        }

        if ($fid>0) {
            //检查某工单某步骤的操作权限
            $sql = "SELECT fh_eid FROM t_flow_history WHERE fh_flowid=".$this->flowId
                ." AND fh_fid=$fid AND fh_faid=$step";
            $employee = $this->db->getField($sql);
            //如果已经指派了员工
            if(!empty($employee)){
                $power['power'] = $eid == $employee ? true : false;
            }
        }


        return $power;
    }

    //获取当前步骤信息
    //fid 工单ID
    public function getCurrentStep($fid){
        $sql = "SELECT fh_faid FROM t_flow_history WHERE fh_fid=$fid AND fh_flowid=".$this->flowId.' AND fh_state=0';
        return $this->db->getField($sql);
    }

    //判断是否是最后一步
    private function _isLastStep($step){
        $action   = $this->getActionList();
        $lastStep = end($action);
        return ($lastStep['fa_id'] == $step);
    }

    /* 修改工单信息
     * author wyh
     * table 表名
     * data array('key'=>value) 修改的数据
     * where 条件
     * return 返回表名进入为下一步操作
     */
    public function update($table,$data,$where){
        if(count(array_filter(func_get_args()))==0){
            $this->error = -110;
            return false;
        }elseif ($this->db->update($table,$data,$where) !== false) {
            return true;
        }else{
            $this->error = -111;
            return false;
        }
    }

    /* 分配任务/重新指派;需要符合部门且具有相关操作权限
     * author wyh
     * @param step 步骤id
     * @param fid 工单ID
     * @param assignTo  分配或转发给谁
     * @param act 操作类型 1 分配 2 转发
     * @param memo 备注
     * */
    public function assign($step,$fid,$act,$assignTo,$memo='工单分配',$eid=0){
        if (count(array_filter(func_get_args()))==0) {
            $this->error = -103;  //参数输入有误
            return false;
        }else{
            $sql = "SELECT e_id,e_departmentID FROM t_employee WHERE e_id='$assignTo' AND e_state=1";
            $assignTo = $this->db->getRow($sql);
            if (!$assignTo) {
                $this->error = -107;    //所指派的员工信息有误
                return false;
            }
            //如果是分配操作则检查该步骤是否已被分配
            $sql = "SELECT fh_eid FROM t_flow_history WHERE fh_fid=$fid AND fh_faid=$step AND fh_flowid=".$this->flowId;
            $fh_eid = $this->db->getField($sql);

            if($act == 1 && !empty($fh_eid)) {
                $this->error = -104;//"该步骤已经受理，无需分配";
                return false;
            }
            //检查指派的员工是否是受理了该步骤
            if ($fh_eid && $fh_eid == $assignTo['e_id']) {
                $this->error = -105;    //该步骤已经是指派员工受理，不需要重复指派";
                return false;
            }

            $eid = empty($eid) ? $_SESSION['userID'] : $eid;
            //修改该步骤的操作员工
            $data = array(
                'fh_eid' => $assignTo['e_id'],
            );

            $res = $this->update('t_flow_history',$data,' fh_flowid='.$this->flowId." AND fh_fid=$fid AND fh_faid=$step");
            if ($res === false) {
                $this->error = -106;    //分配失败;
                return false;
            }
            //插入一条特殊的处理（分配，转单）
            $history = array(
                'fh_flowid' => $this->flowId,
                'fh_fid'    => $fid,
                'fh_faid'   => $act,
                'fh_eid'    => $eid,
                'fh_state'  => 1,
                'fh_time'   => F::mytime(),
                'fh_memo'   => trim($memo),
            );
            $res = $this->db->insert('t_flow_history',$history);
            if ($res < 1) {
                $this->error = -106;    //分配失败;
                return false;
            }

            if (!$this->_flowMsg($fid,$step,$assignTo['e_id'])) {
                $this->error = -106;
                return false;
            }

            return $assignTo;   //返回已经指派的员工信息
        }
    }

    /* 撤销任务;需要符合部门且具有相关操作权限 */

    /* 记录任务历史 */
    /**
     * 添加表单历史记录

     * @param $fagid  工单id

     * @param $faid 步骤ID
     * @param $eid 员工ID
     * @param int $state 操作状态:如果多分支,那么1即为通过,-1为不通过
     * @param $form 表单内容
     * @param string $file 相关文件
     * @param string $memo 备注
     * @return int
     */
    public function flowHistory($fid, $faid, $state = 0, $eid = 0, $form = '', $file = '', $memo = ''){
        if(empty($fid) || empty($faid)){
            $this->error = -21;
            return false;
        }

        $history = array(
            'fh_flowid'	 => $this->flowId,
            'fh_fid'	 => $fid,
            'fh_state'	 => $state,
            'fh_eid'	 => ($eid !== 0) ? $eid : $_SESSION['userID'],
            'fh_file'	 => $file,
            'fh_memo'	 => $memo,
            'fh_form'	 => $form,
            'fh_faid'	 => $faid
        );

        /* 判断已有的操作历史中最后一条记录是否是该操作 */
        $sql = "SELECT fh_id, fh_faid FROM `t_flow_history` WHERE fh_fid='$fid' and fh_faid='$faid' ORDER BY fh_time DESC LIMIT 1";
        $result = $this->db->getRow($sql);

        if($result){//如果最后一条操作记录是和当前步骤一致,那么只需要修改
            $where = " fh_id = '".$result['fh_id']."'";
            if(false === $this->db->update('t_flow_history', $history, $where)){
                $this->error = -22;
                return false;
            }
        }else{
            $history['fh_time'] = F::mytime();
            if(1 < $this->db->insert('t_flow_history', $history)){
                $this->error = -23;
                return false;
            }
        }

        return $this->activeNextStep($fid, $faid,$history['fh_eid'],$state,$memo);
    }

    /* 通过key取得step */
    private function _getStepByKey($key){
        $step = false;
        foreach($this->flowJson->nodeDataArray as $k => $node){
            if(isset($node->key) && $node->key == $key){
                return isset($node->step) ? $node->step : false;
            }
        };

        return $step;
    }

    /* 通过step取得key */
    private function _getKeyByStep($step){
        $idx = $this->_getStepIdx($step);
        if($idx){
            return $this->flowJson->nodeDataArray[$idx]->key;
        }else{
            return false;
        }
    }

    /* 取得当前步骤可达下一步 */
    private function _nextStepList($step){
        $key = $this->_getKeyByStep($step);
        if(!$key){
            return false;
        }
        $nextStepsList = [];
        foreach($this->flowJson->linkDataArray as $k => $v){
            if($v->from == $key){
                $nextStep = $this->_getStepByKey($v->to);
                if($nextStep){
                    $nextStepsList[$nextStep] = [
                        'to'	  => $v->to,
                        'visible' => isset($v->visible) ? $v->visible : 0,
                        'text'	  => isset($v->text) ? $v->text : '',
                        'val'	  => isset($v->val) ? $v->val : 1
                        ];
                }
            }
        }
        return $nextStepsList;
    }

    //上一个步骤
    private function _preStep($fid,$step,$state = -1){
        $nextStepsList = $this->_nextStepList($step);
        foreach($nextStepsList as $k => $v){
            if($v['val'] == $state){
                return array($k=>$v);
            }
        }
        return false;
    }

    /* 根据任务当前步骤的完成状态自动判断下一步操作 */
    /*
    $fid 工单id
    $step 当前步骤ID
    $state 当前步骤的完成状态
    $memo 上一步骤的备注
     */
    public function activeNextStep($fid, $step, $eid, $state = 1,$memo=''){
        if($state == 0){
            return true;
        }

        $res           = false;
        $currentStep   = $step;
        $nextStepsList = $this->_nextStepList($step);

        foreach($nextStepsList as $k => $v){
            if($v['val'] == $state){//指向路径,操作被激活
                if(!$this->flowHistory($fid, $k, 0, '')){//写入到操作历史
                    $this->error = -31;
                    return false;
                }
                $currentStep = $k;
            }
        }

        try{
            $updData = array(
                'flow_lastStepEid'   => $eid,
                'flow_lastStep'      => $step,
                'flow_lastStepState' => $state,
                'flow_lastStepTime'  => F::mytime(),
                'flow_currentStep'   => $currentStep,
            );
            /* 如果不是结束步骤 */
            if ($currentStep != 10) {
                $updData['flow_currentStepEid'] = '';
                $updData['flow_currentStepOrg'] = '';
            }
            $field = $this->db->tableIndex($this->flowTable);
            $field = array_shift($field);


            //修改工单的信息
            if($this->db->update($this->flowTable,$updData,"$field=$fid") === false){
                throw new Exception(-32);
            }

            //撤销或者不通过，直接发站内信给会员
            if ($state < 0) {
                $flowInfo = $this->getCurrentFlowInfo($fid);
                if (empty($flowInfo)) {
                    throw new Exception(-44);
                }

                $targetType = 32 == strlen(strval(trim($flowInfo['flow_uid']))) ? 3 : 4; //3-会员 4-雇员

                $data = array(
                    'targets' => array("{$targetType}-{$flowInfo['flow_uid']}"),
                    'title'   => '你申请的工单已被驳回',
                    'type'    => 1,
                );

                $flowName = current($this->getParentFlowInfo('flow_name'));
                $url = "<a href=\'/flow/flowUser?fid=$fid\' class=\'active ajaxify\'><i class=\'fa fa-edit\'></i> 查看</a>";
                $data['content'] = "您申请的【{$flowName}】工单已经被 {$eid} 驳回，工单号【{$fid}】,原因：$memo:, $url";

                if (32 != strlen($eid)) {
                    $sitemsg = new sitemsg();
                    $sitemsg->save($data);
                }
            }elseif (!($this->_flowMsg($fid,$currentStep,$eid))) {
                //分送站内信给操作人和分配人
                throw new Exception(-33);
            }
            return true;
        }catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }

    /* 工单消息推送
     * fid 任务ID
     * step 工单步骤id
     * */

    private function _flowMsg($fid, $step, $eid=0) {
        if ($step == 10) {
            return true;
        }

        $sql = "SELECT fh_eid FROM t_flow_history WHERE fh_fid=$fid AND fh_faid=$step AND fh_flowid=".$this->flowId;
        $fhEid = $this->db->getField($sql);

        //--- 为什么不用sitemsg类，因为sitemsg->save 使用了事务，而工单的操作也使用了事务
        $saveData = array(
            'mb_uid'     => 'dttx00000', //系统用户
            'mb_ctime'   => F::mytime(),
            'mb_title'   => '',
            'mb_content' => '',
            'mb_type'    => 1,
        );

        //记录操作日志
        $saveData['mb_content'] = '有工单需要你--';

        $sql = 'SELECT flow_url,flow_table FROM t_flow WHERE flow_id='.$this->flowId;

        $flowInfo   = $this->db->getRow($sql);
        extract($flowInfo);
        $url        = "<a href=\'?return=%s&root=3\' class=\'active ajaxify\'><i class=\'fa fa-edit\'></i> %s</a>";
        $data       = array();

        $fa_name = $this->db->getField("SELECT fa_name FROM t_flow_action WHERE fa_id=$step");
        $flow_name = $this->db->getField('SELECT flow_name FROM t_flow WHERE flow_id='.$this->flowId);
        $dealTitle = "处理工单----$fa_name";
        $paramerUrl = urlencode($flow_url."?fid=$fid");
        $dealContent = $saveData['mb_content'].sprintf($url,$paramerUrl,$fa_name);

        if (empty($fhEid)) {
            $saveData['mb_title'] = '为工单分派权限';
            $saveData['mb_content'] .= sprintf($url,$paramerUrl,'分配权限');
            $idx     = $this->_getStepIdx($step);
            if (!$this->flowJson->nodeDataArray[$idx]->powerAssign->workers) {
                $this->error = -306;
                return false;
            }

            $targets = array_unique($this->flowJson->nodeDataArray[$idx]->powerAssign->workers);
            array_push($data,array('saveData'=>$saveData,'targets'=>$targets));

            //通知指定的默认操作者
            $workers = $this->getPowerWorker($step);
            if ($workers) {
                $saveData['mb_title'] = $dealTitle;
                $saveData['mb_content'] = $dealContent;
                array_push($data,array('saveData'=>$saveData,'targets'=>$workers));
            }

            $flowType = $this->db->getField("SELECT flow_type FROM t_flow WHERE flow_id=".$this->flowId);

            //属于业务工单将通知会员
            if (!$flowType) {
                $field = current($this->db->tableIndex($flow_table));
                $uid = $this->db->getField("SELECT flow_uid FROM $flow_table WHERE $field=$fid");
                if (!empty($uid)) {
                    $saveData['mb_title']   = "您申请的【{$flow_name}】最新进度";
                    $saveData['mb_content'] = "您申请的【{$flow_name}】，现在已经处于【{$fa_name}】阶段，请您耐心等待......";
                    array_push($data,array('saveData'=>$saveData,'targets'=>array($uid)));
                }
            }

            if (!$targets && !$workers) {
                $this->error = -307;
                return false;
            }
        }else{
            $saveData['mb_title']   = $dealTitle;
            $saveData['mb_content'] = $dealContent;
            $targets[]              = $eid;
            array_push($data,array('saveData'=>$saveData,'targets'=>$targets));
        }
        $fields = ['mbt_mbID', 'mbt_targetType', 'mbt_targetID'];
        foreach ($data as $v) {
            $row   = $this->db->insert('t_mailbox', $v['saveData']);
            $mb_id = $this->db->getLastID();
            $values = array();

            if (!$row) {
                $this->error = '-101';
                return false;
            }

            if(!is_array($v['targets']) || count($v['targets'])<1){
                $this->error = '-101';
                return false;
            }

            foreach($v['targets'] as $t){
                //消息推送给会员或雇员
                $targetType = strlen($t) == 32 ? 3 : 4;  // 3-会员 4-雇员
                $values[] = array($mb_id, $targetType, $t);
            }

            if ($this->db->inserts('t_mailbox_target', $fields, $values)<1) {
                $this->error = '-101';
                return false;
            }
        }
        return true;
    }

    //获取当前任务某一步的完成情况
    public function getFlowStepState($fid,$step){
        if (!intval($step) || !intval($fid)) {
            return -1;
        }
        return $this->db->getField("SELECT fh_state FROM t_flow_history WHERE fh_fid=$fid AND fh_faid=$step AND fh_flowid=".$this->flowId);
    }

    /* 通过任务步骤及步骤的完成状态,取出任务的分支名称 */
    /*
     * $fid 任务id
     * $step 步骤id
     * $state 步骤的完成状态,非0
     */
    public function getLinkByStepState($fid, $step, $state){
        $key = $this->_getKeyByStep($step);
        $text = '';
        if($state == 0){
            return $text;
        }
        foreach($this->flowJson->linkDataArray as $k => $v){
            if($v->from == $key){
                if(isset($v->val) && isset($v->text) && $v->val == $state){
                    $text = $v->text;
                }
            }
        }
        return $text;
    }

    /* 返回雇员有默认操作权限的操作列表 */
    public function defaultActionList($eid = ''){
        /* 如果是work后台操作,那么eid不需传参数过来 */
        if(empty($eid) && isset($_SESSION['userID'])){
            $eid = $_SESSION['userID'];
        }
        /* 如果没有eid,那么退出 */
        if($eid == ''){
            $this->error = -401;
            return false;
        }
        /* 遍历json查询符合条件的action */
        $_steps = [];

        foreach($this->flowJson->nodeDataArray as $k => $node){
            if(isset($node->power) && isset($node->power->workers) && in_array($eid, $node->power->workers)){
                $_steps[] = $node->step;
            }
        };
        return $_steps;
    }

    /* 返回雇员有分配任务权限的操作列表 */
    public function assignActionList($eid = ''){
        /* 如果是work后台操作,那么是eid不需传参数过来 */
        if(isset($_SESSION['userID'])){
            $eid = $_SESSION['userID'];
        }
        /* 如果没有eid,那么退出 */
        if($eid == ''){
            $this->error = -401;
            return false;
        }
        /* 遍历json查询符合条件的action */
        $_steps = [];

        foreach($this->flowJson->nodeDataArray as $k => $node){
            if(isset($node->powerAssign) && isset($node->powerAssign->workers) && in_array($eid, $node->powerAssign->workers)){
                $_steps[] = $node->step;
            }
        };
        return $_steps;
    }


    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  添加审批人
        * @param aid    审批id
        * @param checkEid   审批人id
        * @author wyh
        * @Returns
     */
    /* ----------------------------------------------------------------------------*/
    public function addChecker($approvalID, $checkEid, $table='t_flow_approval_history'){
        $options = array_filter(func_get_args());
        if (empty($options)) {
            return false;
        }

        $insertData = array(
            'fah_flowid' => $this->flowId,
            'fah_aid'    => $approvalID,
            'fah_eid'    => $checkEid,
            'fah_state'  => 0,
            'fah_time'   => F::mytime(),
        );

        $res = $this->db->insert($table,$insertData);

        if (1 != $res) {
            $this->error = '-101';
            return false;
        }

        return true;
    }

    /* --------------------------------------------------------------------------*/
    /**
        * @Synopsis  提交审批
        * @Param $approvalID 审批申请ID
        * @Param $state 状态 -1不通过 1-通过 -2 撤销
        * @Param $checkEid 审批人
        * @Param $nextCheckEid 下一步审批人
        * @Returns
     */
    /* ----------------------------------------------------------------------------*/
    public function submit($approvalID, $state, $checkEid, $memo='', $nextCheckEid=''){
        if (empty($approvalID) || empty($checkEid)) {
            $this->erro = '-301';
            return false;
        }

        $doRes = $updRes = false;
        $now   = F::mytime();

        $updHistoryData = array(
            'fah_state'     => $state,
            'fah_checkTime' => $now,
            'fah_memo'      => $memo,
        );

        $where = " fah_flowid={$this->flowId} AND fah_aid=$approvalID AND fah_state=0 AND fah_eid='{$checkEid}'";
        $updCheckRes = $this->db->update('t_flow_approval_history',$updHistoryData,$where);
        //echo $this->db->lastSql();exit;
        if (false === $updCheckRes) {
            $this->error = '-302';
            return false;
        }

        $currentEid = $checkEid;
        if (!empty($nextCheckEid) && 1 == $state) {
            $doRes = $this->addChecker($approvalID,$nextCheckEid);
            if (!$doRes) {
                return false;
            }
            $currentEid = $nextCheckEid;
            $approvalState = 0;
        }else{
            $approvalState = $state;
        }

        $indexName = pos($this->db->tableIndex($this->flowTable));
        $preg      = "/\w+_/";
        preg_match_all($preg,$indexName,$matchRes);
        $preFix = pos(pos($matchRes));

        //修改申请表的信息
        $updApployData = array(
            $preFix.'state'      => $approvalState,
            $preFix.'currentEid' => $currentEid,
            $preFix.'lastTime'   => F::mytime(),
        );

        $res = $this->db->update($this->flowTable,$updApployData," {$preFix}id=$approvalID");

        if ($res < 0) {
            $this->error = '-303';
            return false;
        }

        return true;
    }

    /* 返回错误信息 */
    public function getError() {
        //echo $this->error;
        return $this->error;
    }
}
