<?php

class employee_json extends worker {

    function run() {
		$options = $this->options;
		$db = new MySql();
		$cache = new cache();
		$search	  = isset($options['search']) ?  $options['search'] : '';
		$cacheKey = 'employees4select2';
		$result = $cache->get($cacheKey);
		if(!$result){
			$sql = "SELECT p.dm_code AS pcode, p.dm_name AS pname, o.dm_code AS dcode, o.dm_name AS dname, e.e_id AS eid, e.e_name AS ename, d.dt_name AS duty, e.e_charName AS eChar FROM t_employee AS e 
			LEFT JOIN 
			t_organization AS o
			ON e.e_departmentID = o.dm_id
			LEFT JOIN
			t_organization AS p
			ON SUBSTR(o.dm_code FROM 1 FOR 4) = p.dm_code
			LEFT JOIN
			t_duty AS d
			ON e.e_dutyID=d.dt_id
			ORDER BY eChar ASC";
			$result = $db->getAll($sql);
			if(!$result){
				$this->show(message::getJsonMsgStruct('1002'));
				exit;
			}
			$cache->set($cacheKey, $result);			
		}
		
		
		$employees = [];
		foreach($result as $v){
			$employees[$v['pcode']]['id'] = $v['pcode'];
			$employees[$v['pcode']]['text'] = $v['pname'];
			$employees[$v['pcode']]['children'][] = [
				'id'	=> $v['eid'],
				'name'	=> $v['ename'],
				'eChar'	=> $v['eChar'],
				'duty'	=> $v['duty'],
				'dname'	=> $v['dname'],
			];
		}
		
		$html = '';
		foreach($employees as $o){
			if(isset($o['children'])){//optgroupoptgroup
                $html .= '<optgroup label="' . $o['text'] . '">';
                foreach ($o['children'] as $v) {
                    $html .= '<option value="' . $v['id'] . '"';
                    //$html .= (in_array($k, $selected)) ? ' selected="selected"' : '';
					$html .= ' data-char="' . $v['eChar'] . '"';
					$html .= ' data-duty="' . $v['duty'] . '"';
					$html .= ' data-dname="' . $v['dname'] . '"';
                    $html .= '>' . $v['name'] . '</option>';
                }
                $html .= '</optgroup>';				
			}else{//option
                $html .= '<option value="' . $v['id'] . '"';
                //$html .= (in_array($key, $selected)) ? ' selected="selected"' : '';
                $html .= '>' . $v['name'] . '</option>';
				
			}
		}
		echo $html;
		$employees = array_merge($employees, []);
		
		$this->show(message::getJsonMsgStruct('1001', $employees));
		
    }

}
