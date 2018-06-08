<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/18
 * Time: 17:25
 * author : aupl 
 */

class openSdk {
    private $open;
    public function __construct(){
        require(FRAMEROOT . '/lib/open_php/lib/inc.php');
        $this->open = new com\dttx\www\Open();
    }
    
    /*
     * @param  $data type : array    must
     * @param  $path type : string   must
     * */
    public  function request($data, $path){
        if(!is_array($data)){
            return false;
        }
        
        try {
            $result = $this->open->request($path, $data);
            if(!$result){
                throw new Exception( '调用api失败' );
            }
            
            $result = json_decode($result, true);
            if(!$result){
                throw new Exception('接口返回数据不是json格式');
            }
            
            return $result;
            
        } catch (Exception $e) {
            return '操作出错。错误信息：' . $e->getMessage();
        }
        
    }
}
