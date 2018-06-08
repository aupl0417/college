<?php

/* ClassName: News
 * Memo:News class
 * Version:1.0.0
 * EditTime:2012-05-02
 * Writer:flybug
 * */

class help
{

//    private $id = NULL;
//    private $typeid = NULL;
//    private $title = NULL;
//    private $body = NULL;
//    private $source = "";
//    private $writer = NULL;
//    private $creatdate = NULL;
//    private $countnum = 0;
//    private $istop = 0;
//    private $keyword = NULL;
//    private $pubdate = NULL;

    private $id;
    private $root;
    private $typename = "";
    private $count = 0;
    private $code = "";

    public function __construct()
    {
        $this->creatdate = F::mytime();
        $this->pubdate = $this->creatdate;
    }

    //添加信息
    public function add($vartab)
    {
        $db = new MySql();
        return $db->InsertRecord('c_help', $vartab);
    }

    //编辑信息
    public function edit($id, $vartab, $flag = 'n_id', $db = NULL)
    {
        $db = is_null($db) ? new MySql() : $db;
        return $db->UpdateRecord('c_help', $id, $vartab, $flag);
    }

    //根据信息id删除信息
    public function del($id)
    {
        $db = new MySql();
        return $db->DeleteRecord('c_help', $id, 'n_id');
    }

    //点击计数加一
    public function accInc($id = "")
    {
        $db = new MySql();
        $sql = "UPDATE c_help SET countnum = countnum + 1 WHERE id = '$id'";
        return $db->Execute($sql);
    }

    //获取id对应的文章
    public function getHelpById($id = "", $db = NULL)
    {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "SELECT * FROM c_help WHERE n_id = '{$id}'";
        $db->Query($sql);
        return $db->getCurRecode(PDO::FETCH_ASSOC);
    }

    //得到新闻类型的名称
    //增加了$an参数，参数 为条件，例如root<>0，用于筛选root的数据。

    public function getTypeNameByTypeId($id, $an = '', $db = NULL)
    {
        $db = is_null($db) ? new MySql() : $db;
        if ($id == '') {
            $an = ($an == '') ? $an : " where $an ";
            $sql = 'SELECT t_id,t_typename FROM c_helptype ' . $an;
        } else {
            $an = ($an == '') ? $an : " and $an";
            $sql = "SELECT t_id,t_typename FROM c_helptype WHERE t_id = '{$id}'" . $an;
        }
        $db->Query($sql);
        return $db->getAllRecodes(PDO::FETCH_ASSOC);
    }

    //通过rootid获取所有该类型的 类型名称
    public function getTypeNameByRootid($rid, $db = NULL)
    {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "SELECT t_id,t_typename,t_root FROM c_helptype WHERE t_root = '{$rid}'";
        $db->Query($sql);
        return $db->getAllRecodes();
    }

    //通过rootid获取所有该类型的 类型名称
    public function getTypeNameById($id, $db = NULL)
    {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "SELECT t_id,t_typename,t_root FROM c_helptype WHERE t_id = '{$id}'";
        $db->Query($sql);
        return $db->getAllRecodes();
    }

    //通过一串类型id，返回一串类型名称
    public function getTypeNamesByIds($ids, $db = NULL)
    {
        $ids = F::addYh($ids);
        $db = is_null($db) ? new MySql() : $db;
        $ids2 = str_replace("'", "", $ids);
        $sql = "SELECT t_id,t_typename,t_root FROM c_helptype WHERE t_id in ({$ids}) ";
        $db->Query($sql);
        return $db->getAllRecodes(PDO::FETCH_ASSOC);
    }

    //通过t_id获取该类型的 所有父类型名称 用于首页的导航
    public function getParentTypeById($id, $db = NULL)
    {
        $db = is_null($db) ? new MySql() : $db;
        $sql = "SELECT t_id,t_typename,t_root FROM c_helptype WHERE t_id = '{$id}'";
        $db->Query($sql);
        $allType = $db->getAllRecodes(PDO::FETCH_ASSOC);
        $allType = $allType[0];
        $typeHtml = "<a href=\"?model=help&rid=" . $allType['t_id'] . "\" target='_parent'>" . $allType['t_typename'] . "</a>";
        if (intval($allType['t_root']) > 0) $typeHtml = $this->getParentTypeById($allType['t_root']) . " &raquo; " . $typeHtml;
        return $typeHtml;
    }

    //读取资讯三级分类
    public function getAllType($field, $typewhere) {
        $db = new MySql() ;
        $sql = "select $field from t_news_type WHERE '$typewhere'";
        return $db->getRow($sql);

    }

    //读出t_news_type表中指定项目,并处理成可转Options格式的数组,最多读两级
    public function getTypeName($group = false) {//$type:属性类型;$group:格式化输出
        $cache = new cache();
        $cacheId = 'newsty_' . var_export($group, true);
        $cachenewsty = $cache->get($cacheId);
        if ($cachenewsty) {
            return $cachenewsty;
        } else {
            $db = new MySql();
            $sql = "SELECT * FROM `t_news_type` WHERE  `nt_code` LIKE '14%' AND SUBSTR(`nt_code`, 3,2)<>'00' AND SUBSTR(`nt_code`, 5, 2)<>'00'";
            $typenews = $db->getAll($sql);

            if ($group) {
                $newtype = array();
                foreach ($typenews as $types) {
                    $id = $types['nt_id'];
                    $root = $types['nt_root'];
                    $typename = $types['nt_typename'];
                    $code = $types['nt_code'];
//                    if (substr($code,7,2) != '00') {
//                        $newtype[$id]['label'] = $typename;
//                    } else {
//                        $newtype[$root]['options'][$id] = $typename;
//                    }

//                    if (substr($code,7,2) != '00') {
//                        $newtype[$id] = $typename;
//                    } else {
//                        $newtype[$root][$id] = $typename;
//                    }
                }
                $cache->set($cacheId, $newtype);
                return $newtype; //self::array2Options($newAttrs, [], true);
            } else {
                $typenews = array_column($typenews, 'nt_typename', 'nt_id');
                $cache->set($cacheId, $typenews);
                return $typenews;
            }
        }
    }

//    public static function array2Options($optsArray, $selected = array(), $group = false) {
//        $optionsTemp = '';
//        foreach ($optsArray as $key => $val) {
//            if (is_array($val) && $group) {
//                $optionsTemp .= '<optgroup label="' . $val['label'] . '">';
//                foreach ($val['options'] as $k => $v) {
//                    $optionsTemp .= '<option value="' . $k . '"';
//                    $optionsTemp .= (in_array($k, $selected)) ? ' selected="selected"' : '';
//                    $optionsTemp .= '>' . $v . '</option>';
//                }
//                $optionsTemp .= '</optgroup>';
//            } else {
//                $optionsTemp .= '<option value="' . $key . '"';
//                $optionsTemp .= (in_array($key, $selected)) ? ' selected="selected"' : '';
//                $optionsTemp .= '>' . $val . '</option>';
//            }
//        }
//        return $optionsTemp;
//    }
}

?>