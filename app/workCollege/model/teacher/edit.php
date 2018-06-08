<?php

class edit extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);
    }
	
    function run() {
		$id = $this->options['id'];
        $sql = "select id,username,trueName,email,br_name as branchName,mobile,te_source,te_birthday,te_level,te_isLeave,te_photo,te_IDNum,te_eduLevel,te_fromAcademy,te_description,te_sex,te_source,te_workExperience,te_courseReward,te_teachGrade,tl_name as teacherLevel from tang_ucenter_member
            left join tang_branch on tangCollege=br_id 
		    left join tang_teacher on id=te_userId 
		    left join tang_teacher_level on te_level=tl_id 
            where identityType=1 and id='{$id}'";
        
        $db = new MySql();
        $result = $db->getRow($sql);
        
        $courseType   = array(
            array('id' => 0, 'name' => '公开课'),
            array('id' => 1, 'name' => '定制课'),
            array('id' => 2, 'name' => '其它')
        );
        $teacherLevel = $db->getAll("select tl_id,tl_name from tang_teacher_level");//讲师级别
        $branchList   = $db->getAll('select br_id,br_name from tang_branch');//暂时选择所有学院
        $eduLevel     = array(
            array('levelId'=>0, 'levelName'=> '小学及以下'),
            array('levelId'=>1, 'levelName'=> '高中或中专'),
            array('levelId'=>2, 'levelName'=> '专科'),
            array('levelId'=>3, 'levelName'=> '本科'),
            array('levelId'=>4, 'levelName'=> '研究生'),
            array('levelId'=>5, 'levelName'=> '博士'),
            array('levelId'=>6, 'levelName'=> '博士后')
        );
        
//         $source      = array(
//             array('sourceId' => 1, 'sourceName' => '总部内训'),
//             array('sourceId' => 2, 'sourceName' => '分院内训'),
//             array('sourceId' => 3, 'sourceName' => '外聘教师'),
//         );
        $source = array(1=>'总部内训', 2=> '分院内训', 3=>'外聘教师');
        
        $result['te_sex'] = $result['te_sex'] == 0 ? '女' : '男';
        $result['teacherLevel'] = $this->getSelectString($teacherLevel, 'tl_id', 'tl_name', $result['teacherLevel']);
        $result['branchList']   = $this->getSelectString($branchList, 'br_id', 'br_name', $result['branchName']);
        $result['gradeList']    = $this->getSelectString($courseType, 'id', 'name', '', $result['te_teachGrade']);
        $result['eduLevel']     = $this->getSelectString($eduLevel, 'levelId', 'levelName', $result['te_eduLevel']);
        //$result['source']       = $this->getSelectString($source, 'sourceId', 'sourceName', '', $result['te_source']);
        $result['source']       = $source[$result['te_source']];
        $result['te_photo'] = $result['te_photo'] ? TFS_APIURL . '/' . $result['te_photo'] : '{_TEMP_PUBLIC_}/images/none.png';

		$this->setReplaceData($result);
        $this->setTempAndData();
        $this->show();
    }
    
    private function getSelectString($data, $keyId, $keyName, $name, $key=''){
        if(!is_array($data)){
            return $data;
        }
        
        $string = '';
        foreach($data as $k=>$val){
            if($val[$keyName] == $name || $val[$keyId] == $key){
                $string .= '<option value="'.$val[$keyId].'" selected="selected">' . $val[$keyName] . '</option>';
            }else {
                $string .= '<option value="'.$val[$keyId].'">' . $val[$keyName] . '</option>';
            }
        }
        
        return $string;
    }
}
