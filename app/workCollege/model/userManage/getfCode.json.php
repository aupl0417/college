<?php
/**
 * 根据用户昵称获取推荐码
 * @param nickname  用户昵称
*/
class getfCode_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, []);
        $this->db = new MySql();
    }
  function run() {

      $options = $this->options;
      $nickname = $options['nickname'] ? $options['nickname'] : "";

      if($nickname == ""){
          return $this->show(message::getJsonMsgStruct('1002',''));
      }else {
          //检查是否已经收藏
          $sql = "SELECT u_code FROM t_user WHERE u_nick ='".$nickname."'";
          $code = $this->db->getField($sql);
          if($code){
              return $this->show(message::getJsonMsgStruct('1001',$code));
          }else{
              return $this->show(message::getJsonMsgStruct('1002',''));
          }
      }

  }
}
