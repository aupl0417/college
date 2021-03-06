<?php

/**
 *
 * 分页类
 *
 * @author aupl
 * 
 * 根据不同的场景，定制高效的分页系统
 * @param $table        表名 或者 表名+join语句
 * @param $fields       查询字段
 * @param $pagenow      分页（默认为1）
 * @param $pagesize     分页大小（默认为20）
 * @param $menustyle    设置显示样式(默认为0)
 * @param $where        查询条件
 * @param $order        查询排序
 */
//交易记录扩展分页类
class page extends divpage
{

    public function __construct($table, $fields = '*', $pagenow = 1, $pagesize = 20, $menustyle = 1, $where = '', $order = '', $function = 'go')
    {
        parent::__construct('', '', $fields, $pagenow, $pagesize, $menustyle, '', $function);
        $this->wherestr = ($where == '') ? ' where 1=1' : $where;
        $this->orderstr = $order;
        $this->table    = $table;
        $this->function = $function;
    }

    //覆盖DivPage
    public function getDivPage()
    {
        //起止号
        $_beginRow = ($this->pagenow - 1) * $this->pagesize; //所在页数-1*页面显  示数=当前页记录开始的数
        //$_endRow = $_beginRow + $this->pagesize;//此处修正了取记录的数量。修正了limit参数的第二个为每页数量
        $_limitString = " LIMIT {$_beginRow},{$this->pagesize}"; //从当前页开始数的地方取页面显示数量的记录（limit选取）

        $sql1 = "SELECT count(*) FROM $this->table $this->wherestr";
        $sql2 = "SELECT $this->fields FROM $this->table $this->wherestr $this->orderstr limit $_beginRow,$this->pagesize";
//        echo $sql2;die;
        $db = new MySql();
        //得到页数据
        $count = $db->getField($sql1); //执行sql语句
        $this->pagedata = $db->getAll($sql2);
        //得到导航菜单
        $lastpage = ceil($count / $this->pagesize); //向上舍入为整数  将行数划断
        /* 2013-3-28日修改，小白：当记录为空时，最后页数为零；翻页失败 */
        $lastpage = $lastpage == 0 ? 1 : $lastpage;

        $this->pageinfo = array(
            "msg" => "您要查找的记录",
            "total" => $count, //总记录数
            "pagenow" => $this->pagenow, //当前页的值
            "fpage" => 0 < $this->pagenow - 1 ? $this->pagenow - 1 : 1, //上一页
            "npage" => $lastpage < $this->pagenow + 1 ? $lastpage : $this->pagenow + 1, //下一页
            "lastpage" => $lastpage, //最后一页
            "pagecount" => $lastpage, //页面数量
            "pagesize" => $this->pagesize, //页面容量（当前页面显示的记录数）
            "root" => $this->root
        );
        $_para = array(//需要替换的内容
            "_replace" => $this->pageinfo
        );
        $this->setMenuStyle($this->menustyle, '{$this->function}'); //设置显示样式
        if ($this->menustyle == 0) {
            $this->getListStyleMenu();
        } else {
            $myhtml = new myHTML(); //新的myhtml类（class_myHTML）
            $this->pagemenu = $myhtml->getHTML($this->pagemenu, $_para); //加载参数			
        }
    }

    public function setMenuStyle11($ms = 1)
    {
        $this->menustyle = $ms;
        switch ($this->menustyle) {
            case
            1 :
                $this->pagemenu .= "<replace value=\"total|pagenow|fpage|npage|lastpage|pagecount|pagesize\">";
                $this->pagemenu .= "<div class=\"padding-top-25 text-right\">";
                $this->pagemenu .= "<ul class=\"pagination\">";
                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({fpage})\"> 上一页 </a> </li>";
                for ($i = 1; $i <= $this->pageinfo['pagecount']; $i++) {
                    if ($this->pagenow == $i){
                        $this->pagemenu .= "<li class=\"active\"> <a href=\"javascript:{$this->function}($i);\"> $i </a> </li>";
                    }else{
                        $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}($i);\"> $i </a> </li>";
                    }
                }
                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({npage})\"> 下一页 </a> </li>";
                $this->pagemenu .= "</ul></div>";
                $this->pagemenu .= "</replace>";
                break;

            case
            2 :
                $this->pagemenu .= "<replace value=\"total|pagenow|fpage|npage|lastpage|pagecount|pagesize\">";
                $this->pagemenu .= "<div class=\"padding-top-25 text-right\">";
                $this->pagemenu .= "<ul class=\"pagination\">";
                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}(1);\">首页</a></li> ";
                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({fpage});\">上一页</a> </li>";
                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({npage});\">下一页</a> </li>";
                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({lastpage});\">尾页</a> </li>";
                $this->pagemenu .= "</ul></div>";
                $this->pagemenu .= "</replace>";
                break;

            case
            3 :
                $this->pagemenu .= "<replace value=\"total|pagenow|fpage|npage|lastpage|pagecount|pagesize\">";
                $t = floor(($this->pageinfo['pagenow'] - 1) / 2) * 2 + 1; //向下舍入，floor(1.9)=1
                $this->pagemenu .= "<div class=\"padding-top-25 text-right\">";
                $this->pagemenu .= "<ul class=\"pagination\">";
//                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({fpage})\"> 上一页 </a> </li>";
                for($i = $t; $i < min(($t + 2), $this->pageinfo['lastpage'] + 1); $i++){
                    if($i == $this->pageinfo['pagenow']){
                        $this->pagemenu .= "<li class=\"active\"> <a href=\"javascript:{$this->function}($i);\"> $i </a> </li>";
                    }else{
                        $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}($i);\"> $i </a> </li>";
                    }
                }
                if ($i > 3) {
                    $p = $t - 1;
                    $this->pagemenu = "<li class=\"ellipsis\"> <a href=\"javascript:{$this->function}($p)\" target=\"_self\">……</a> </li>" . $this->pagemenu;
                }
                $this->pagemenu = '<li> <a href=\"javascript:{$this->function}({fpage})\" target="_self">上一页</a> </li>' . $this->pagemenu;
                if ($i < $this->pageinfo['lastpage']) {
                    $p = $t + 2;
                    $this->pagemenu .= "<li class=\"ellipsis\"> <a href=\"javascript:{$this->function}($p)\" target=\"_self\">……</a></li><li><a>{$this->pageinfo['lastpage']}</a> </li>";
                }
                $this->pagemenu .= '<li> <a href=\"javascript:{$this->function}({npage})\" target="_self">下一页</a> </li>';

//                $this->pagemenu .= "<li> <a href=\"javascript:{$this->function}({npage})\"> 下一页 </a> </li>";
                $this->pagemenu .= "</ul></div>";
                $this->pagemenu .= "</replace>";
                break;


        }
    }

    public function getListStyleMenu()
    {
        $t = floor(($this->pageinfo['pagenow'] - 1) / 4) * 4 + 1; //向下舍入，floor(1.9)=1
        for ($i = $t; $i < min(($t + 4), $this->pageinfo['lastpage'] + 1); $i++) {
            if ($i == $this->pageinfo['pagenow']) {
                $this->pagemenu .= "<li><a class=\"current\" href=\"javascript:{$this->function}($i)\" target=\"_self\">$i</a></li>";
            } else {
                $this->pagemenu .= "<li><a href=\"javascript:{$this->function}($i)\" target=\"_self\">{$i}</a></li>";
            }
        }

        if ($i > 5) {
            $p = $t - 1;
            $this->pagemenu = "<li><a class=\"ellipsis\" href=\"javascript:{$this->function}($p)\" target=\"_self\">……</a></li>" . $this->pagemenu;
        }
        $this->pagemenu = '<li><a href="javascript:' . $this->function . '(' . $this->pageinfo["fpage"] . ')" target="_self">上一页</a></li>' . $this->pagemenu;
        if ($i < $this->pageinfo['lastpage']) {
            $p = $t + 4;
            $this->pagemenu .= "<li><a class=\"ellipsis\" href=\"javascript:{$this->function}($p)\" target=\"_self\">……</a></li><li><a>{$this->pageinfo['lastpage']}</a></li>";
        }
        $this->pagemenu .= '<li><a href="javascript:' . $this->function . '(' . $this->pageinfo["npage"] . ')" target="_self">下一页</a></li>';
    }

    public function getMenu()
    {
        return $this->pagemenu;
    }

}
