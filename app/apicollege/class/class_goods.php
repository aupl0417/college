<?php

/**
 * 商品基类
 * 商品的  添加 删除 修改 查询 等等
 * @author flybug
 * @version 1.0.0
 */
class goods
{

    private $g_id = NULL;//商品id
    private $g_taobaoid = NULL;//商品的淘宝id
    private $g_name;//商品名称
    private $g_pic;//图片地址
    private $g_type;//商品类型
    private $g_gys;//供应商类型     淘宝/天猫   系统参数
    private $g_sid;//卖家id
    private $g_ctime;//创建时间
    private $g_etime;//修改时间
    private $a_type = NULL;//活动类型
    private $a_postage = "";//邮资
    private $a_price = NULL;//价格
    private $a_ctime = NULL;//创建时间
    private $a_question = 0;//活动的问题
    private $a_answer = 0;//活动的答案
    private $a_hit = NULL;//申请次数
    private $a_percent = NULL;//赔付比率
    private $a_memo = NULL;//备注

    public function __construct()
    {
        //获取最近操作的时间
        $this->a_ctime = F::mytime();
        $this->pubdate = $this->a_ctime;
    }


    //添加商品
    public function add($vartab)
    {

        $db = new MySql();
        $sql = "SELECT count(*) FROM fly_goods where g_taobaoid = {$vartab['g_taobaoid']} ";
        $db->Query($sql);
        $row = $db->getResultCol();
        if ($row != 0) {
            return -1;
        }
        return $db->InsertRecord('fly_goods', $vartab);
    }

    //修改商品
    public function edit($id, $vartab, $flag = 'g_id', $and)
    {
        $db = new MySql();
        return $db->UpdateRecord('fly_goods', $id, $vartab, $flag, $and);
    }

    //删除商品
    public function del($id, $flag, $and)
    {
        $db = new MySql();
        return $db->DeleteRecord('fly_goods', $id, $flag, $and);
    }

    //商品被申请计数加一
    public function inchit($id = "")
    {

    }

    //根据id获取商品信息@@
    public function getGoodsInfoById($id, $fildes = '*', $and = '')
    {
        if ($and != '') {
            $and = " and  $and ";
        }
        $sql = "SELECT {$fildes} FROM v_goods where g_id = $id $and ORDER by g_ctime DESC ";
        $db = new MySql();
        $db->Query($sql);
        return $db->getAllRecodes(PDO::FETCH_ASSOC);
    }

    //判断该商品是否允许被删除
    public function IsDelByGid($gid)
    {

        $sql = "SELECT count(*) FROM fly_action where a_gid = $gid and a_state in (1,2,3,4,5,-4,-5)";
        $db = new MySql();
        $db->Query($sql);
        $row = $db->getAllRecodes();
        if ($row[0][0] == 0) {
            return 1;//允许删除
        }
        return 0;//不允许删除
    }

    //根据gid判断该商品信息是否能够修改
    public function IsEditByGid($gid)
    {
        $sql = "SELECT count(*) FROM fly_action where a_gid = $gid and a_state in (1,2,3,5,-4,-5)";
        $db = new MySql();
        $db->Query($sql);
        $row = $db->getAllRecodes();
        if ($row[0][0] == 0) {
            return 1;//允许修改
        }
        return 0;//不允许修改
    }

    //根据卖家sid，获取商家商品的条数
    //不同的参数，返回的活动的条件不同
    //不需要，在翻页中实现
    public function getGoodsInfoBySid()
    {
    }

    //根据获取全部的类名称
    public function getType()
    {
        $sql = "SELECT * FROM fly_goodstype";
        $db = new MySql();
        $db->Query($sql);
        return $row = $db->getAllRecodes(PDO::FETCH_ASSOC);

    }

    //根据商品的淘宝id在淘宝网查询淘宝信息；
    //增加字段参数(默认为全部字段)
    public function getGoodsInfoByTaobaogid($gid, $fields, $token)
    {
        $tb = new taobaoAPI();
        $p = array(
            'num_iid' => $gid,
            'fields' => $fields
        );
        return $tb->execute('taobao.item.get', $p, $token);
    }

    //通过类型的id得到类型的名称
    public function getGoodsTypeNameByGTid($tid, $an = '')
    {
        $db = new MySql();

        if ($tid == '') {
            $an = ($an == '') ? $an : " where $an";
            $sql = 'SELECT t_id,t_typename FROM fly_goodstype' . $an;
        } else {
            $an = ($an == '') ? $an : " and $an";
            $sql = "SELECT t_id,t_typename FROM fly_goodstype WHERE t_id = '{$tid}'" . $an;
        }
        $db->Query($sql);
        return $db->getAllRecodes(PDO::FETCH_ASSOC);
    }


    //根据信息类型得到下拉选择框
    //$typename-选择框控件的id
    //$default-当前选择值
    //$msg-校验出错信息
    //$act-下拉选择后的校验函数
    public function getGoodsTypeBox($typename, $default = 0, $msg = '', $act = '', $an)
    {
        $data = $this->getGoodsTypeNameByGTid('', $an);
        if ($msg != '') {
            $msg = " error=\"$msg\"";
        }
        $act = ($act != '') ? " onchange=\"f$typename()\"" : '';
        $str = "<select id=\"$typename\" name=\"$typename\"{$msg}{$act}>";
        //加入缺省项

        $str .= "<option value=\"0\" selected=\"selected\">-- 请选择 --</option>";
        foreach ($data as $v) {
            $str .= "<option value=\"%s\" %s>%s</option>";
            $str = sprintf($str, $v['t_id'], ($v['t_id'] == $default) ? "selected=\"selected\"" : '', $v['t_typename']);
        }
        return $str . "</select>";
    }

}