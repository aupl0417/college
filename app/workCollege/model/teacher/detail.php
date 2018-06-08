<?php

class detail extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);
    }
	
    function run() {
		$id = $this->options['id'];
        $sql = "select id,username,trueName,email,br_name as branchName,mobile,te_source,te_isLeave,te_IDNum,te_photo,te_birthday,te_eduLevel,te_fromAcademy,te_description,te_sex,te_workExperience,te_courseReward,tl_name,te_teachGrade from tang_ucenter_member
            left join tang_branch on tangCollege=br_id 
		    left join tang_teacher on id=te_userId 
		    left join tang_teacher_level on te_level=tl_id 
            where identityType=1 and id='{$id}'";
        
        $db = new MySql();
        $result = $db->getRow($sql);
        
        $source = array(1=>'总部内训', 2=> '分院内训', 3=>'外聘教师');
        $eduLevel = array('小学及以下','高中或中专','专科','本科','研究生','博士','博士后');
        $courseLevel = array(1 => '初级', 2 => '中级课及以下', 3 => '高级课及以下', 4 => '其它课', 5 => '所有等级');
        $courseType  = array('公开课', '定制课', '其它');

        $result['te_photo'] = $result['te_photo'] ? TFS_APIURL . '/' . $result['te_photo'] : '{_TEMP_PUBLIC_}/images/none.png';
        $result['te_sex'] = $result['te_sex'] == 1 ? '男' : '女';
        $result['source'] = $source[$result['te_source']];
        $result['branchName'] = empty($result['branchName']) ? '中国总部' : $result['branchName'];
        $level = empty($result['te_eduLevel']) ? 0 : $result['te_eduLevel'];
        $result['eduLevel'] = $eduLevel[$level];
        $result['courseType'] = $courseType[$result['te_teachGrade']];
        
		$this->setReplaceData($result);
        $this->setTempAndData();
        $this->show();
    }
}
