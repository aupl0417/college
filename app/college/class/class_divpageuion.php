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
class divUionPage extends divpage
{

    public function __construct($fields = '*', $pagenow = 1, $pagesize = 20, $menustyle = 1, $where = '', $order = '')
    {
        parent::__construct('', '', $fields, $pagenow, $pagesize, $menustyle, '', 'go');
        $this->wherestr = ($where == '') ? ' where 1=1' : $where;
        $this->orderstr = $order;
    }

    //覆盖DivPage
    public function getDivPage()
    {
        //起止号
        $_beginRow = ($this->pagenow - 1) * $this->pagesize; //所在页数-1*页面显  示数=当前页记录开始的数
        //$_endRow = $_beginRow + $this->pagesize;//此处修正了取记录的数量。修正了limit参数的第二个为每页数量
        $_limitString = " LIMIT {$_beginRow},{$this->pagesize}"; //从当前页开始数的地方取页面显示数量的记录（limit选取）

        $sql1 = "SELECT count(*) FROM t_union_companyex INNER JOIN t_user ON u_id=uc_uid $this->wherestr";
        $sql2 = "SELECT $this->fields FROM t_union_companyex INNER JOIN t_user ON u_id=uc_uid $this->wherestr order by uc_validateContractTime desc limit $_beginRow,$this->pagesize";
//        dump($sql2);
        $db = new MySql();
        //得到页数据
        $count = $db->getField($sql1); //执行sql语句
//        $this->pagedata = $db->getAll($sql2);
        $all_shop = $db->getAll($sql2);

		$authList = [
			'union' =>[13]
		];
		$user = new user();
        foreach($all_shop as $k=>$v){
			$authImgs = $user->getUserAuthImgs($v['uc_uid'], $authList);
            if(isset($authImgs[133])){
                $shop_img = $authImgs[133];
            }else{
                $shop_img = UNION_DEFAULT_IMG;
            }
            $all_shop[$k]['cover_img'] = $shop_img;//F::authImageExist($v['uc_uid'], '13-3');
        }
        $this->pagedata = $all_shop;
//        dump($this->pagedata);
        //得到导航菜单
        $lastpage = ceil($count / $this->pagesize); //向上舍入为整数  将行数划断

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
        $this->setMenuStyle($this->menustyle); //设置显示样式
        if ($this->menustyle == 0) {
            $this->getListStyleMenu();
        } else {
            $myhtml = new myHTML(); //新的myhtml类（class_myHTML）
            $this->pagemenu = $myhtml->getHTML($this->pagemenu, $_para); //加载参数			
        }
    }

    public function setMenuStyle($ms = 1)
    {
        $this->menustyle = $ms;
        switch ($this->menustyle) {
            case 1 :
                $this->pagemenu .= "<replace value=\"total|pagenow|fpage|npage|lastpage|pagecount|pagesize\">";
                $this->pagemenu .= "<div class=\"padding-top-25 text-right\">";
                $this->pagemenu .= "<ul class=\"pagination\">";
                $this->pagemenu .= "<li>  <span>共".$this->pageinfo['pagecount']."页</span></li>";
                $this->pagemenu .= "<li> <a href=\"javascript:go(1)\"> 首页 </a> </li>";
                $this->pagemenu .= "<li> <a href=\"javascript:go({fpage})\"> 上一页 </a> </li>";
                if($this->pagenow > 2){
                    $p_start = $this->pagenow - 2;
                }else{
                    $p_start = 1;
                }
                //取5个页面选择按钮
                $btn_num = 0;
                for ($i = $p_start; $i <= $this->pageinfo['pagecount']; $i++) {
                    if ($this->pagenow == $i){
                        $this->pagemenu .= "<li class=\"active\"> <a href=\"javascript:go($i);\"> $i </a> </li>";
                    }else{
                        $this->pagemenu .= "<li> <a href=\"javascript:go($i);\"> $i </a> </li>";
                    }
                    if($btn_num >= 4){
                        break;
                    }
                    $btn_num++;
                }
                $this->pagemenu .= "<li> <a href=\"javascript:go({npage})\"> 下一页 </a> </li>";
                $this->pagemenu .= "<li> <a href=\"javascript:go(".$this->pageinfo['pagecount'].")\"> 尾页 </a> </li>";
                $this->pagemenu .= "</ul></div>";
                $this->pagemenu .= "</replace>";
                break;
        }
    }

}
