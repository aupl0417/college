<?php

/**
 *
 * 扩展分页系统
 *
 * @author flybug
 * @version 1.0.0
 *
 * 根据不同的场景，定制高效的分页系统 2014-08-21
 *
 */
//交易记录扩展分页类
class divBusinessPage extends divpage
{

    public function __construct($model = '', $fields = '*', $pagenow = 1, $pagesize = 20, $menustyle = 1, $root = '', $scriptFunc = 'ago', $where = '', $order = '')
    {
        parent::__construct('', $model, $fields, $pagenow, $pagesize, $menustyle, $root, $scriptFunc);
        $this->wherestr = ($where == '') ? ' where 1=1' : $where;
        $this->orderstr = $order;
    }

    //覆盖DivPage
    public function getDivPage($table)
    {
        //起止号
        $_beginRow = ($this->pagenow - 1) * $this->pagesize; //所在页数-1*页面显  示数=当前页记录开始的数
        //$_endRow = $_beginRow + $this->pagesize;//此处修正了取记录的数量。修正了limit参数的第二个为每页数量
        $_limitString = " LIMIT {$_beginRow},{$this->pagesize}"; //从当前页开始数的地方取页面显示数量的记录（limit选取）

        $sql1 = "SELECT count(*) FROM v_{$table}business $this->wherestr";
        $sql2 = "SELECT $this->fields FROM v_{$table}business $this->wherestr and bs_id <= (select bs_id from a_{$table}business $this->wherestr $this->orderstr limit $_beginRow,1) order by bs_id desc limit $this->pagesize";
        //die($sql2);

        $db = new MySql();

        $db->Query($sql1); //执行sql语句
        //得到页数据
        $count = $db->getResultCol(); //返回记录的总行数
        $db->Query($sql2);
        $this->pagedata = $db->getAllRecodesEx($this->resulttype); //返回数据
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
        if ($this->menustyle == 0) {
            $this->getListStyleMenu();
        } else {
            $myhtml = new myHTML(); //新的myhtml类（class_myHTML）
            $this->pagemenu = $myhtml->getHTML($this->pagemenu, $_para); //加载参数			
        }
    }

}
