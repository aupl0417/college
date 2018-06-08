<?php
/*=============================================================================
#     FileName: index.json.php
#         Desc: 课室列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-29 10:09:07
#      History:
=============================================================================*/
class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[500401]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE tra_state=1 ';
        if (!isset($options['search'])) {
            //$where .= " AND cta.cta_startTime>'".date('Y-m-d 00:00:00')."' ";
        }

        if (isset($options['clID']) && !empty($options['clID'])) {
            //$where .= " AND cta.cta_classId={$options['clID']}";
        }

        $sql = "SELECT tra.*,ar.a_name FROM tang_trainingsite tra LEFT JOIN tang_area ar ON tra.tra_areaId=ar.a_code $where";
        $result = $dataGrid->create($this->options,$sql);

        $property         = F::getAttrs(5);
        $trainingSiteType = F::getAttrs(6);

        if (!$result['data']) {
            die(F::dtEmpty());
        }

        $classList = array(-1=>'font-red-thunderbird','font-red-thunderbird','font-green-jungle','font-blue');
        $opStr  = "<a href='/%s/%s?_ajax=1&id=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        foreach ($result['data'] as &$v) {
            $v['DT_RowId'] = 'row_'.$v['tra_id'];
            $v['property'] = $property[$v['tra_property']];
            $v['type']     = $trainingSiteType[$v['tra_type']];

            $v['op'] = sprintf($opStr,'classRoomManage','editSite',$v['tra_id'],'#formModal','fa-edit','编辑')
                ."<a href='javascript:void(0)' class='btn-xs blue delSite' data-name='{$v['tra_name']}' data-id='{$v['tra_id']}'><i class='fa fa-trash'></i> 删除</a>";
        }
        echo json_encode($result);
    }
}
