<?php
/*=============================================================================
#     FileName: delEmployee.php
#         Desc: 删除雇员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-28 09:30:18
#      History:
#      Paramer: 
=============================================================================*/
class delEmployee extends baseApi{
   function run(){
       $options = $this->data;

       if (!isset($options['e_id']) || empty($options['e_id'])) {
           $this->apiReturn(1002, '参数错误', '雇员id参数不能为空');
       }

       $delRes = apis::request('college/api/delEmployee.json',['e_id'=>$options['e_id']],true);

       if (1001 != $delRes['code']) {
           $this->apiReturn(1002, '错误', $delRes['data']);
       }

       $this->apiReturn(1001, '删除大唐大学雇员信息成功', $delRes['data']);
   } 
}
