<?php
/*=============================================================================
#     FileName: loginAction.json.php
#         Desc: 登录
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-23 11:35:18
#      History:
#      Paramer:
=============================================================================*/

class loginAction_json extends guest {

    function run() {
        $options  = $this->options;

        if (!isset($options['password'],$options['username']) || empty($options['username']) || empty($options['password'])) {
            die($this->show(message::getJsonMsgStruct('1002','参数错误')));
        }

        $param = array(
            'password' => F::getSuperMD5($options['password']),
            'username' => trim($options['username']),
        );

        $user = new user();
        if (!$user->uniqueUserInfo(5, $this->options['code'], '', 'code')) {
          die($this->show(message::getJsonMsgStruct('2001'))); //验证码错误
        }

        try{
            $db = new MySql();
            //erp授权
            $sdk = new openSdk();

            $loginInfo = $sdk->request($param, '3login/college');
            //$loginInfo = json_decode($loginInfo,true);

            if ('1001' != $loginInfo['id']) {
                throw new Exception($loginInfo['msg'],'1002');
            }

            //$user = new user();
            //$result = $user->checkEmployee($data['username'],$data['password']);
            //if ($result != '2100') {
            //    throw new Exception('登录失败','1002');
            //}

            //$returndata = $db->getRow( "SELECT * FROM t_employee WHERE e_id='".$options['username']."' LIMIT 1" );
            $this->saveEmployee($loginInfo['info']);
            $url = isset($_SESSION['backUrl']) ? $_SESSION['backUrl'] : 'http://'.WORKERURL;
            $this->show(message::getJsonMsgStruct('2100', array('url' => $url)));

        }catch(Exception $e){
            die($this->show(message::getJsonMsgStruct($e->getCode(),$e->getMessage())));
        }
    }

   private function saveEmployee($employeeInfo){
       $_SESSION['userID']         = $employeeInfo['e_id'];
       $_SESSION['userNick']       = $employeeInfo['e_name'];
       $_SESSION['userDepartment'] = $employeeInfo['e_departmentID'];
       $_SESSION['userType']       = 2;
       $_SESSION['depName']        = $employeeInfo['depName'];
       $_SESSION['dutyID']         = $employeeInfo['e_dutyID'];
       $_SESSION['dutyName']       = $employeeInfo['posName'];
       $_SESSION['joinTime']       = $employeeInfo['e_joinTime'];
       $_SESSION['state']          = $employeeInfo['e_state'];
       $_SESSION['tel']            = $employeeInfo['e_tel'];
       $_SESSION['certNum']        = $employeeInfo['e_certNum'];

       $db = new MySql();
       $_SESSION['userPower'] = $db->getField("SELECT GROUP_CONCAT(p_id) FROM tang_power_work");
       
   }
}
