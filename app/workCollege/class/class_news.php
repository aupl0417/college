<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/28
 * Time: 15:25
 */

class news {

//    private $n_title = NULL; //新闻标题
//    private $n_content = NULL; //新闻内容
//    private $n_createTime = NULL; //新闻创建时间
//    private $n_createId = NULL; // 创建人的ID
//    private $n_createNick = NULL; //创建人昵称

    public function __construct()
    {
    }

    /*
     * 添加新闻处理
     */
//    public function add($data, $db = NULL){
//        $db = is_null($db)? new MySql() : $db;
//        $sql = "insert into t_news VALUES ($data)";
//        if($db->exec($sql) == 1){
//            return 1;
//        }else{
//            return 0;
//        }
//
//    }


    //添加信息
    public function add($vartab,$type)
    {
        $db = new MySql();
        $result = $db->insert('t_news', $vartab);
        $msg = $db->getField("select nt_typename from t_news_type where nt_code='".$vartab['n_type']."'");
        $update = array();
        //记录操作日志
        if($type == 11){
            $type = 3010104;
        }else if($type == 12){
            $type = 3010204;
        }else if($type == 13){
            $type = 3010304;
        }else{
            $type = 3010404;
        }
        $update['memo'] = '发布'.$msg;
        log::writeLogMongo($type, 't_news', $db->getLastID(), array_merge($vartab,$update));
        return $result;
    }

    /*
     * 删除指定新闻
     */
    public function del($newsid) {
        $db = new MySql();
        //记录操作日志
        $type = $db->getField("select n_type from t_news where n_id='".$newsid."'");
        $msg = $db->getField("select nt_typename from t_news_type where nt_code='".$type."'");
        $type = substr($type,0,2);
        if($type == 11){
            $type = 3010102;
        }else if($type == 12){
            $type = 3010202;
        }else if($type == 13){
            $type = 3010302;
        }else{
            $type = 3010402;
        }
        $sql = "delete from t_news where n_id = '$newsid' ";
        $result = $db->exec($sql) == 1;
        $update['memo'] = '删除-类型：'.$msg;
        log::writeLogMongo($type, 't_news', $newsid, array_merge(['n_id' => $newsid],$update));
        return $result;
    }

    /*
     * 读取新闻，读取指定的新闻内容,读取一行
     */
    public function getNewsByNewsid($newsid, $field= '*') {
        $db = new MySql() ;
        $sql = "select $field from t_news WHERE n_id = '$newsid'";
        return $db->getRow($sql);
    }

    /*
     * 修改，修改指定的新闻内容
     */
    public function editNewsByNewsid($newsid, $data) {
        $db = new MySql();
        
        $type = $db->getField("select n_type from t_news where n_id='".$newsid."'");
        $msg = $db->getField("select nt_typename from t_news_type where nt_code='".$type."'");
        $type = substr($type,0,2);
        if($type == 11){
            $type = 3010103;
        }else if($type == 12){
            $type = 3010203;
        }else if($type == 13){
            $type = 3010303;
        }else{
            $type = 3010403;
        }
        $result = $db->update('t_news',$data, "n_id='$newsid'");
        $data['memo'] = $msg.'改图/内容';
        log::writeLogMongo($type, 't_news', $newsid, array_merge($newsid,$data));
        return $result;
    }

    /*
     * 将新闻标记为已发布
     */
    function setPublishByNewsid($newsid, $db = NULL) {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "UPDATE t_news SET n_state = '1' WHERE n_id = '$newsid'";
        return $db->exec($sql) == 1;
    }


}