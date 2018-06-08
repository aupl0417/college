<?php
/*=============================================================================
#     FileName: addEmployee.php
#         Desc: 添加雇员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-28 09:30:24
#      History:
#      Paramer: 
=============================================================================*/
class addEmployee extends baseApi{
   function run(){
       $options = $this->data;

       $employeeInfo = array(
           'e_certNum',
           'e_charName',
           'e_companyID',
           'e_createTime',
           'e_departmentID',
           'e_dutyID',
           'e_firstPwd',
           'e_id',
           'e_joinTime',
           'e_loginPwd',
           'e_name',
           'e_photo',
           'e_powerHash',
           'e_sex',
           'e_state',
           'e_tel',
           'e_uid',
       );

       if (empty($options)) {
           $this->apiReturn(1002, '参数错误', '参数不能为空');
       }

       $employeeInfo = array_fill_keys($employeeInfo,0);
       $employeeInfo = array_intersect_key($options,$employeeInfo);

       if (empty($employeeInfo)) {
           $this->apiReturn(1002, '参数错误', '雇员信息不能为空');
       }

       $addRes = apis::request('college/api/addEmployee.json',$employeeInfo,true);

       if (1001 != $addRes['code']) {
           $this->apiReturn(1002, '错误', $addRes['data']);
       }

       $this->apiReturn(1001, '修改大唐大学雇员信息成功', $addRes['data']);
   } 
}
