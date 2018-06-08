<?php
/* 
	通过资讯平台编码取得下级联动
 */
class newsTypeRule_json extends worker {

    function run() {
		$options = $this->options;
 		if(isset($options['id'])){
			$id = F::fmtNum($options['id']);
			$id = $id ? $id : 0;
		}else{
			$id = 0;
		}

		$db = new MySql();
		if($id == 0){//取出所有一级分类
			$sql = "select nt_code as code, nt_typename as `name`, nt_id as id, SUBSTR(nt_code, 1, 2) as `key` from t_news_type where nt_id=3";
			$result = $db->getAll($sql);
			if($result){
				$newstype = array();
				foreach($result as $v){
					$newstype[$v['key']] = array('name'	   => $v['name']);
				}
				$result = $newstype;
			}else{
				$this->show(message::getJsonMsgStruct('3012', '参数错误'));
				exit;
			}

		}
		else{

			switch(strlen($id)){
				case 2:
					$sql = "select nt_code as code, nt_typename as `name`, nt_id as id, SUBSTR(nt_code, 1, 4) as `key` from t_news_type where nt_code like '".$id."%0000' and nt_code <>'".$id."000000' ORDER BY `nt_code` ASC";
					break;
				case 4:
					$sql = "select nt_code as code, nt_typename as `name`, nt_id as id, SUBSTR(nt_code,1,6) as `key` from t_news_type where nt_code like '".$id."%00' and nt_code <>'".$id."0000' ORDER BY `nt_code` ASC";
					break;
				case 6:
					$sql = "select nt_code as code, nt_typename as `name`, nt_id as id, nt_code as `key` from t_news_type where nt_code like '".$id."%' and nt_code <>'".$id."00' ORDER BY `nt_code` ASC";
					break;
				default:
					$this->show(message::getJsonMsgStruct('3012', '参数错误'));
					exit;
			}

			$result = $db->getAll($sql);

			if($result){
				$newstype = array();
				foreach($result as $v){
					$newstype[$v['key']] = array('name'	   => $v['name']);
				}
				$result = $newstype;
			}else{
				$this->show(message::getJsonMsgStruct('3012', '参数错误'));
				exit;
			};
		}


		$this->show(message::getJsonMsgStruct('3010', $result));
    }

}
